<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        // $results = Posts::where('title', 'like', "%{$query}%")
        //     ->orWhere('description', 'like', "%{$query}%")
        //     ->orWhereJsonContains('tags', $query)
        //     ->get();

        $results = Posts::search($query)->get();

        dd($results, $query);

        return response()->json($results);
    }
}
