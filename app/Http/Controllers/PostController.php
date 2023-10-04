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
use App\Models\Point;
use App\Models\Transaction;
use Illuminate\Support\Str;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class PostController extends Controller {

    public function __construct() {
        return $this->middleware( 'auth:sanctum' )->only( [ 'store', 'update', 'destroy', 'edit', 'create' , 'postReact'  ] );
    }

    /**
    * Display a listing of the resource.
    */

    public function index(Request $request) {
        $account = Account::where('username', $request->query('username'))->first();
        $getType = $request->query('type');
        $perPage = $request->get('per_page', 10);

        try {
            if($getType){
                $postsCollection = Posts::where('file_type', $getType )
                // inRandomOrder()->
                -> orderBy( 'updated_at', 'desc' )
                ->paginate($perPage);
            }
            else {
                $postsCollection = Posts::
                // inRandomOrder()->
                orderBy( 'updated_at', 'desc' )
                ->paginate( $perPage );
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
    if( !$thumbUri) return null;
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

    try {
        DB::beginTransaction();

        $post = new Posts();
        $puid =  uniqid( Str::lower(config( 'app.name' )).'-' );
        $post->puid = $puid;
        $post->tags = json_decode( $request->input( 'tags') );
        $post->description =  $request->input('description');
        $post->title =  $request->input('title');
        $post->thumbnail_url =   $this->moveThumb($request->file( 'thumbnail' ), $puid );
        $post->post_type =  PostTypes::class::getValue( $postType );
        $post->account_id =  $user->account->id;

        if ( $request->hasFile('upload') ) { 
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
            $post->mime_type = $uploadInfo['mime_type'];
            $post->file_type = $uploadInfo['file_type'];
            $post->file_url = $uploadInfo['file_url'];
            $post->source_qualities = $uploadInfo['source_qualities'];
        }

        $post = $post->save();
        DB::commit();
        return response()->json( ['message' => 'post created successfully',  'post' => $post ], 201 );
    } catch ( \Throwable $th ) {
        //throw $th;
        DB::rollback();
        return response()->json( [
            'message' => 'something went wrong: '.$th->getMessage(), $postType
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

public function postView(Request $request ) {
    $post = Posts::where( 'puid', $request->input('puid') )->first();
    $owner = $post->account;
    $message = 'nicely done 🌟!';
    $point = new Point();

    if ( !$post ) return response()->json( [ 'message' => 'Post not found' ], 404 );
    if($request->has('views')){
    //    $post->rewards += 1;
    //    $point->balance += 10;
       $point->account_id = $post->account->id;
       $point->save();
       $post->views += (int) $request->input('views');
    }

    $post->save();
    $owner->save();
    return response()->json(['message' => $message  ] );
}

public function postReward(Request $request) {
    $post = Posts::where('puid', $request->input('puid'))->first();

    if (!$post) {
        return response()->json(['error' => 'Post not found'], 404);
    }

    $rewardAmount = $request->input('rewards');

    if (!$rewardAmount) {
        return response()->json(['error' => 'Reward amount not provided'], 400);
    }

    $owner = $post->account;
    $viewer  = auth()->guard('sanctum')->user();

    if ($viewer) { 
        $viewerAccount = $viewer->account;
        $rewardAmount *= 0.7;
        if ($owner->id !== $viewerAccount->id) {
            $viewerAccount->points += $rewardAmount * 0.3;
            $viewerAccount->save();
        }else {
            $rewardAmount = 0;
        }
    }

    $owner->points += (int) $rewardAmount;
    $owner->save();

    $post->rewards += (int) $rewardAmount;
    $post->save();

    return response()->json(['message' => 'Rewards distributed successfully', $viewer ]);
}


public function postReact(Request $request ) {
    $user  = request()->user();
    if(!$user) return;
    $post =  $user->posts()->where('puid', $request->input('puid'))->first();
    if ( !$post ) return;
    $reaction = $request->has( 'reacted' );
    if (! $reaction) return;
    $liked = $user->likes()->where('post_id', $post->id)->first();  
    $liked ?  $post->likedByAccounts()->detach($user->account->id) : $post->likedByAccounts()->attach($user->account->id);
    return response()->json(['message' => 'Reaction saved']);
}

public function update( Request $request, string $puid ) {
    $user  = request()->user();
    if(!$user) return;
    $post =  $user->posts()->where('puid', $puid)->first();
    if ( !$post ) return response()->json( [ 'message' => 'Post not found'], 404 );
    $account = $user->account;
    $message = 'nicely done 🌟 yup!';

    try {       
        // 
        $post->description = $request->input('description');
        $post->title =  $request->input('title');
        $post->album = $request->input('album');
        $post->year = $request->input('year');
        $post->tags = json_decode( $request->input( 'tags') );

        if($request->hasFile('thumbnail')){
            if($post->thumbnail_url){
                    $thumbnail = storage_path('app/'.$post->thumbnail_url);
                    if($thumbnail !== $request->file('thumbnail')->getPath()){
                    if(file_exists( $thumbnail)) 
                        unlink( $thumbnail);
                    }
                }
            $post->thumbnail_url = $this->moveThumb($request->file( 'thumbnail' ), $puid);
        }
        if($request->hasFile('upload')){
            if($post->file_url){
                    $fileurl = storage_path('app/'.$post->file_url);
                    if( $post->file_url !== $request->file('upload')->getPath()){
                        if(file_exists($fileurl))
                        unlink($fileurl);
                }
            }
            $uploadedFile = $this->moveUploadedFiles($request->file('upload'), $puid);
            $post->file_url =  $uploadedFile['file_url'];
            $post->source_qualities = $uploadedFile['source_qualities'];
            $post->mime_type = $uploadedFile[ 'mime_type' ] ;
            $post->file_type = $uploadedFile[ 'file_type' ] ;
        }
        if($request->has('downloadable'))
            $post->downloadable = $request->input('downloadable');

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

   
        $account->save();
        $post->save();
        return response()->json(['message' => $message , $post ] );
    } catch (\Throwable $th) {
         return response()->json(['message' => 'An issue has occurred while tryng to update post.: '.$th->getMessage(), ], 500);
    }
}

/**
* Remove the specified resource from storage.
*/

public function destroy( string $puid ) {
    $user  = request()->user();
    if($user){
      $post =  $user->posts()->where('puid', $puid)->first();
        try {
            if($post->thumbnail_url){
                $thumbnail = storage_path('app/'.$post->thumbnail_url);
                if(file_exists( $thumbnail)) 
                unlink( $thumbnail);
            }
            if($post->file_url){
                $fileurl = storage_path('app/'.$post->file_url);
                if(file_exists($fileurl))
                unlink($fileurl);
            }
            $post->delete();
            return response()->json(['message' => 'The post has been successfully deleted.'],200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'An issue has occurred while tryng to delete post.: '.$th->getMessage()], 500);
        }

    }
}
}