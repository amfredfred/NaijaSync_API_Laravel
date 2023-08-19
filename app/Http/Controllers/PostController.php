<?php

namespace App\Http\Controllers;

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
        //
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

        $validatedData = $request->validate( [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'upload' => 'required|mimes:jpeg,png,jpg,gif,mp4,zip,ogg,mp3,webp,webm,gif,mov,mkv|max:5000',
            'location_view' => 'nullable|string',
            'location_download' => 'nullable|string',
        ] );

        $selectedTags = explode( ', ', $request->input( 'tags', '' ) );

        try {

            // Additional data
            $validatedData[ 'owner_id' ] = $request->user()->id;
            $validatedData[ 'ratings' ] = 0;
            $validatedData[ 'duration' ] = 0;
            $thumbnailPath = '';

            DB::beginTransaction();
            $postSlug = Str::slug( $validatedData[ 'title' ] );
            $postSlug = Str::upper( $postSlug ).'-'.Str::random( 5 );

            // Handle thumbnail upload
            if ( $request->hasFile( 'thumbnail' ) ) {
                $thumbnailPath = $request->file( 'thumbnail' );
                $thumbMimeType = $thumbnailPath->getClientOriginalExtension();
                $thumbnailPath = $thumbnailPath->storeAs( 'thumbnails', $postSlug.'.'.$thumbMimeType );
                $relativePath = str_replace( '/storage', '', $thumbnailPath );
                $absoluetPath = storage_path( 'app/' . $relativePath );
                $validatedData[ 'thumbnail_url' ] = $relativePath;
            }

            // Handle file upload
            if ( $request->hasFile( 'upload' ) ) {
                $file = $request->file( 'upload' );
                $fileMimeType = $file->getClientOriginalExtension();
                $fileType = $fileHelper->getFileType( $fileMimeType, true );
                $fileUrl = $file->storeAs( 'posts/'.$fileType.'/'.$fileMimeType, $postSlug.'.'.$fileMimeType );
                $relativePath = str_replace( '/storage', '', $fileUrl );
                $absoluetPath = storage_path( 'app/' . $relativePath );
                $fi = $fileHelper->fi( $absoluetPath );

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
                $validatedData[ 'post_slug' ] = $postSlug;
            }
            $validatedData[ 'tags' ] = $selectedTags;
            $post = Posts::create( $validatedData );
            DB::commit();
            return dd( $post );
            // return redirect()->route( 'posts.index' )->with( 'success', 'Post created successfully.' );
        } catch ( \Throwable $th ) {
            //throw $th;
            DB::rollback();
            dd( $th );
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
