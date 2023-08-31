<?php

namespace App\Http\Controllers;

use App\Enums\PostTypes;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Posts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\FilesHelper;
use Illuminate\Support\Str;

class PostController extends Controller {
    /**
    * Display a listing of the resource.
    */

    public function index() {
        try {
            $postsCollection = Posts::inRandomOrder()
                ->orderBy('created_at', 'desc')
                ->paginate(5);

            $modifiedPosts = $postsCollection->getCollection()->map(function ($post) {
                return new PostResource($post);
            });

            $postsCollection->setCollection($modifiedPosts);

            return response()->json($postsCollection, 200);
        } catch (\Throwable $th) {
            return response()->json([[], 'message' => 'Something went wrong: ' . $th->getMessage()], 500);
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

    public function store( Request $request ) {

        $fileHelper = new FilesHelper();
        $postType = $request->input( 'type' );

        $validatedData = $request->validate( [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'caption' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'upload' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,zip,ogg,mp3,webp,webm,gif,mov,mkv|max:50000',
        ] );

        $selectedTags = json_decode($request->input( 'tags', [] ) );
        $isimportLiink = $request->input( 'import_link' );

        if ( $postType ) {
            $validatedData[ 'post_type' ] = PostTypes::class::getValue( $postType );
        }

        try {
            // Additional data
            $validatedData[ 'owner_id' ] = 1;
            //$request->user()->id
            $thumbnailPath = '';

            DB::beginTransaction();
            $puid =  uniqid( config( 'app.name' ).'-' );

            // Handle thumbnail upload
            if ( $request->hasFile( 'thumbnail' ) ) {
                $thumbnailPath = $request->file( 'thumbnail' );
                $thumbMimeType = $thumbnailPath->getClientOriginalExtension();
                $thumbnailPath = $thumbnailPath->storeAs( 'thumbnails', $puid.'.'.$thumbMimeType );
                $relativePath = str_replace( '/storage', '', $thumbnailPath );
                $absoluetPath = storage_path( 'app/' . $relativePath );
                $validatedData[ 'thumbnail_url' ] = $relativePath;
            }

            // Handle file upload
            if ( $request->hasFile( 'upload' ) ) {
                $file = $request->file( 'upload' );
                $fileMimeType = $file->getClientOriginalExtension();
                $fileType = $fileHelper->getFileType( $fileMimeType );
                $fileUrl = $file->storeAs( 'posts/'.$fileType.'/'.$fileMimeType, $puid.'.'.$fileMimeType );
                $relativePath = str_replace( '/storage', '', $fileUrl );
                $absoluetPath = storage_path( 'app/' . $relativePath );
                $fi = $fileHelper->fi( $absoluetPath );

                if ( !isset( $validatedData[ 'title' ] ) ) {
                    if ( isset( $fi[ 'tags_html' ][ 'id3v2' ][ 'title' ][ 0 ] ) ) {
                        $validatedData[ 'title' ] = $fi[ 'tags_html' ][ 'id3v2' ][ 'title' ][ 0 ];
                        array_push($selectedTags, Str::camel( $validatedData[ 'title' ]));
                    }
                }

                $validatedData[ 'source_qualities' ] = [
                    'original' => [
                        'size' => $fi[ 'filesize' ],
                        'path' => $relativePath,
                        'name' => $fi[ 'filename' ],
                        'format' => $fi[ 'fileformat' ]
                    ]
                ];
                if ( isset( $fi[ 'playtime_string' ] ) )
                $validatedData[ 'source_qualities' ][ 'original' ][ 'duration' ] = [
                    'formatted' => $fi[ 'playtime_string' ],
                    'playtime' => $fi[ 'playtime_seconds' ]
                ];
                if ( isset( $fi[ 'video' ][ 'resolution_x' ] ) )
                $validatedData[ 'source_qualities' ][ 'original' ][ 'width' ] = $fi[ 'video' ][ 'resolution_x' ];
                if ( isset( $fi[ 'video' ][ 'resolution_y' ] ) )
                $validatedData[ 'source_qualities' ][ 'original' ][ 'height' ] = $fi[ 'video' ][ 'resolution_y' ];

                $validatedData[ 'mime_type' ] = $fileMimeType;
                $validatedData[ 'file_type' ] = $fileType;
                $validatedData[ 'file_url' ] = $relativePath;
                $validatedData[ 'puid' ] = $puid;
            }

            $validatedData[ 'title' ] =  $validatedData[ 'title' ]  ?? '';

            //  =
            $validatedData[ 'tags' ] = $selectedTags;
            $post = Posts::create( $validatedData );
            DB::commit();
            return response()->json( [
                'message' => 'post created successfully',
                'post' => $post
            ], 201 );
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

    public function update( Request $request, string $id ) {
        //
    }

    /**
    * Remove the specified resource from storage.
    */

    public function destroy( string $id ) {
        //
    }
}
