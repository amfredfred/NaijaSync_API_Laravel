<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SegmentsController extends Controller {
    public function __invoke( $filePath )  {
        $path = storage_path( 'app/' .   $filePath );

        if ( !file_exists( $path ) ) {
            abort( 404 );

        }

        return response()->file( $path );
    }
}
