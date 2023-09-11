<?php

namespace App\Http\Controllers;

use App\Enums\PostTypes;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Posts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\FilesHelper;
use App\Http\Resources\PostCollection;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Str;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class PostController extends Controller {

    public function __construct() {
        return $this->middleware( 'auth:sanctum' )->only( [ 'store', 'update', 'destroy', 'edit', 'create'  ] );
    }

    /**
    * Display a listing of the resource.
    */

    public function index(Request $request) {
        $account = Account::where('username', $request->query('username'))->first();
        $getType = $request->query('type');

        try {
            if($getType){
                $postsCollection = Posts::where('file_type', $getType )->inRandomOrder()->paginate(20);
            }
            else {
            $postsCollection = Posts::inRandomOrder()
            ->orderBy( 'created_at', 'desc' )
            ->paginate( 5 );
            }

            $modifiedPosts = $postsCollection->getCollection()->map( function ( $post ) use ($account) {
                $post['likes'] = count($post->likedByAccounts);
                if($account){
                   $liked =  $post->likedByAccounts()->where('account_id', $account->id)->first();
                   if($liked) $post['liked'] = true;
                }
                return new PostResource( $post );
            }
        );

        $postsCollection->setCollection( $modifiedPosts );

        return response()->json( $postsCollection, 200 );
    } catch ( \Throwable $th ) {
        return response()->json( [ [], 'message' => 'Something went wrong: ' . $th->getMessage() ], 500 );
    }
}

/**
* Show the form for creating a new resource.
*/

public function create() {

    return view( 'Post.upload' );
}

/**
* Store a newly created resource in storage.
*/

public function moveThumb( $thumbUri, $puid ) {
    $fileHelper = new FilesHelper();
    $thumbMimeType = pathinfo($thumbUri, PATHINFO_EXTENSION);
     $fileType = $fileHelper->getFileType($thumbMimeType);
    if ($fileType === 'video')  
        $thumbMimeType = 'jpg';
    else if ($thumbMimeType  === 'tmp'){
        $thumbMimeType = 'jpg';
    } 
    $customFilename = $puid . '_thumbnail.' . $thumbMimeType;
    $relativePath = 'thumbnails/'  . $customFilename;
    Storage::put($relativePath, file_get_contents($thumbUri), );
    return  $relativePath;
}

public function moveUploadedFiles($file,  $puid) {
    $fileHelper = new FilesHelper();
    $fileMimeType = $file->getClientOriginalExtension();
    $fileType = $fileHelper->getFileType( $fileMimeType );
    $fileUrl = $file->storeAs( 'posts/'.$fileType.'/'.$fileMimeType, $puid.'.'.$fileMimeType );
    $relativePath = str_replace( '/storage', '', $fileUrl );
    $absoluetPath = storage_path( 'app/' . $relativePath );
    $fi = $fileHelper->fi( $absoluetPath );

    $uploadInfo[ 'source_qualities' ] = [
        'original' => [
            'size' => $fi[ 'filesize' ],
            'path' => $relativePath,
            'name' => $fi[ 'filename' ],
            'format' => $fi[ 'fileformat' ]
        ]
    ];
    if ( isset( $fi[ 'playtime_string' ] ) )
    $uploadInfo[ 'source_qualities' ][ 'original' ][ 'duration' ] = [
        'formatted' => $fi[ 'playtime_string' ],
        'playtime' => $fi[ 'playtime_seconds' ]
    ];
    if ( isset( $fi[ 'video' ][ 'resolution_x' ] ) )
    $uploadInfo[ 'source_qualities' ][ 'original' ][ 'width' ] = $fi[ 'video' ][ 'resolution_x' ];
    if ( isset( $fi[ 'video' ][ 'resolution_y' ] ) )
    $uploadInfo[ 'source_qualities' ][ 'original' ][ 'height' ] = $fi[ 'video' ][ 'resolution_y' ];

    $uploadInfo[ 'mime_type' ] = $fileMimeType;
    $uploadInfo[ 'file_type' ] = $fileType;
    $uploadInfo[ 'file_url' ] = $relativePath;
    $uploadInfo[ 'puid' ] = $puid;
    $uploadInfo['absoluetPath'] = $absoluetPath;

    return $uploadInfo;
}

public function store( Request $request ) {

    $postType = $request->input( 'type' );
    $fileHelper = new FilesHelper();

    $user  = request()->user();
    $followers = $user->followers;
    $following = $user->following;

    $validatedData = $request->validate( [
        'title' => 'nullable|string|max:255',
        'description' => 'nullable|string', 
        'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'upload' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,zip,ogg,mp3,webp,webm,gif,mov,mkv|max:50000',
    ] );

    $selectedTags = json_decode( $request->input( 'tags','') );
    $validatedData['description'] = json_decode($request->input('description'), null);
    $isimportLiink = $request->input( 'import_link' );

    if ( $postType ) {
        $validatedData[ 'post_type' ] = PostTypes::class::getValue( $postType );
    }

    try {
        $validatedData[ 'account_id' ] =  $user->account->id;
        DB::beginTransaction();
        $puid =  uniqid( Str::lower(config( 'app.name' )).'-' );

        // Handle thumbnail upload
        if ( $request->hasFile( 'thumbnail' ) ) {
            $validatedData[ 'thumbnail_url' ] = $this->moveThumb($request->file( 'thumbnail' ), $puid );
        } 
        // Handle file upload
        if ( $request->hasFile( 'upload' ) ) { 
            if ( !isset( $validatedData[ 'title' ] ) ) {
                if ( isset( $fi[ 'tags_html' ][ 'id3v2' ][ 'title' ][ 0 ] ) ) {
                    $validatedData[ 'title' ] = $fi[ 'tags_html' ][ 'id3v2' ][ 'title' ][ 0 ];
                    array_push( $selectedTags, Str::camel( $validatedData[ 'title' ] ) );
                }
            }
            $uploadInfo = $this->moveUploadedFiles($request->file( 'upload' ), $puid);
            if(!$request->hasFile('thumbnail')){  
                $fileType = $fileHelper->getFileType($uploadInfo['mime_type']);
                if($fileType === 'video'){
                  try {
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($request->file('upload'));  
                        $thumbnail = $video->frame(TimeCode::fromSeconds(0.1));
                        $customFilename = $puid . '_thumbnail.png' ;
                        $relativePath = 'thumbnails/'  . $customFilename;
                        $thumbnail->save(storage_path('app/'.$relativePath));
                        $validatedData[ 'thumbnail_url' ] = $relativePath ;
                    } catch (\Throwable $th) {
                    //throw $th;
                  }
                }else if ($fileType === 'audio'){

                }
            }
            $validatedData = [...$validatedData, ...$uploadInfo] ;
        }
        $validatedData[ 'tags' ] = $selectedTags;
        $post = Posts::create( $validatedData );
        DB::commit();
        return response()->json( ['message' => 'post created successfully',  'post' => $post ], 201 );
    } catch ( \Throwable $th ) {
        //throw $th;
        DB::rollback();
        return response()->json( [
            'message' => 'something went wrong: '.$th->getMessage()
        ], 500 );
    }

}

/**
* Display the specified resource.
*/

public function show( string $id ) {
    try {
        $post = Posts::find( $id );
        if ( $post ) {
            $post  = new PostResource( $post );
            return response()->json( $post,  200 );
        } else {
            return response()->json( [ 'message'=>'post with id '. $id.' not found' ], 404 );
        }
    } catch ( \Throwable $th ) {
        return response()->json( [ 'message'=>$th->getMessage() ], 500 );
    }
}

/**
* Show the form for editing the specified resource.
*/

public function edit( string $id ) {
    //
}

/**
* Update the specified resource in storage.
*/

public function update( Request $request, string $puid ) {
    $user  = request()->user();
    $post = Posts::where( 'puid', $puid )->first();
    $account = $post->account;
    $message = 'nicely done 🌟!';

    if ( !$post ) return response()->json( [ 'message' => 'Post not found' ], 404 );

    if ( $request->has( 'liked' ) ) {
        $liked = $user->likes()->where('post_id', $post->id)->first();
        if ( $liked ) {
           $post->likedByAccounts()->detach($user->account->id);
            $message = 'Unliked';
        } else {
           $post->likedByAccounts()->attach($user->account->id);
            $message = 'Liked';
        }
    }
    if($request->has('description'))
        $post->description = $request->input('description', null);
    if($request->has('title'))
        $post->title = $request->input('title', null);
    if($request->has('views'))
        $post->views += $request->input('views');
    if($request->has('tags')){
       $tags = json_decode( $request->input( 'tags', [] ) );
       $tags = array_unique(...$post->tags, ...$tags);
       $post->tags = $tags;
    }
    if($request->hasFile('thumbnail'))
        $post->thumbnail_url = $this->moveThumb($request->file( 'thumbnail' ), $puid);
    if($request->has('downloadable'))
        $post->downloadable = $request->input('downloadable');
    if($request->has('album'))
       $post->album = $request->input('album');
    if($request->has('year'))
       $post->year = $request->input('year');
    if($request->has('genre')){
       $genre = json_decode( $request->input( 'genre', [] ) );
       $genre = array_unique(...$post->genre, ...$genre);
       $post->genre = $genre;
    }
    if($request->has('transfer_post_to_username')){
        $newOwner = Account::where('username', $request->input('transfer_post_to_username'))->first();
        if($newOwner){
            $post->account_id = $newOwner->id;
            // $transaction = new TransactionController();
            // $transaction->transferPoints($account, $newOwner, $post->rewards);
        }
    }
    if($request->hasFile('upload')){
        $uploadedFile = $this->moveUploadedFiles($request->file('unpload'), $puid);
        $post->file_url =  $uploadedFile['file_url'];
        $post->source_qualities = $uploadedFile['source_qualities'];
        $post->mime_type = $uploadedFile[ 'mime_type' ] ;
        $post->file_type = $uploadedFile[ 'file_type' ] ;
    }
    if($request->has('rewards')){
        $post->rewards += (int) $request->input('rewards');
        $account->points += $request->input('rewards');
        $account->save();
    }

    $post->save();
    return response()->json(['message' => $message  ] );
}

/**
* Remove the specified resource from storage.
*/

public function destroy( string $puid ) {
    $user  = request()->user();
    $user->posts()->where('puid', $puid)->first()->delete();
}
}