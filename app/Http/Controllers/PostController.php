<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Posts;
use Illuminate\Support\Facades\Storage;

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

        $validatedData = $request->validate( [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location_view' => 'nullable|string',
            'location_download' => 'nullable|string',
            'tags' => 'nullable|array',
            'downloadable' => 'required|boolean',
        ] );

        // Additional data
        $validatedData[ 'owner_id' ] = $request->user()->id;
        $validatedData[ 'ratings' ] = 0;
        $validatedData[ 'file_url' ] = '';
        $validatedData[ 'duration' ] = 0;
        $validatedData[ 'source_qualities' ] = [];
        $validatedData[ 'mime_type' ] = '.mp4';
        $validatedData[ 'mime_type' ] = '.mp4';
        $validatedData[ 'mime_type' ] = '.mp4';

        // Handle thumbnail upload
        if ( $request->hasFile( 'thumbnail' ) ) {
            $thumbnailPath = $request->file( 'thumbnail' )->store( 'thumbnails', 'public' );
            $validatedData[ 'thumbnail_url' ] = Storage::url( $thumbnailPath );
        }

        // Create the post
        $post = Posts::create( $validatedData );

        return redirect()->route( 'posts.index' )->with( 'success', 'Post created successfully.' );

    }

    /**
    * Display the specified resource.
    */

    public function show( string $id ) {
        //
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
