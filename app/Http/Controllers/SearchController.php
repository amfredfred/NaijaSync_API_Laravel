<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Posts;
use Illuminate\Http\Request;

class SearchController extends Controller {
    public function search( Request $request ) {
        $query = $request->input( 'query' );
        $target = $request->input( 'target' );
        $limit = $request->input(('limit'));
        $inradom = $request->input('inrandom');

        $results = Posts::where( 'title', 'like', "%{$query}%" )
        ->orWhere( 'description', 'like', "%{$query}%" )
        ->orWhere( 'post_slug', 'like', "%{$query}%" )
        ->orWhereJsonContains( 'post_genre', $query )
        ->orWhereJsonContains( 'post_genre', $target )
        ->orWhereJsonContains( 'tags', $target )
        ->orWhereJsonContains( 'tags', $query )
        ->orderBy('created_at', 'desc')
         ->limit($limit ?? 10)
        ->get();

        $dataResults = [];
        foreach ( $results as $key => $value ) {
            array_push( $dataResults, new PostResource( $value ) );
        }

        return response()->json( [ 'results'=>$dataResults ], 200 );
    }
}
