<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use Illuminate\Http\Request;

class AccountController extends Controller {

    public function posts( Request $request ) {

        try {
            $user = request()->user();
            $posts = $user->posts()->orderBy('created_at', 'desc')->get();
            $posts = new PostCollection( $posts );
            return response()->json( $posts, 200 );
        } catch ( \Throwable $th ) {
            return response()->json( [ 'message' => $th->getMessage() ], 500 );
        }
    }
}
