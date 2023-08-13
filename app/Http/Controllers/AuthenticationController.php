<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller {
    public function register_account( Request $request ) {
        $validatedData = $request->validate( [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ] );

        $user = User::create( [
            'name' => $validatedData[ 'name' ],
            'email' => $validatedData[ 'email' ],
            'password' => Hash::make( $validatedData[ 'password' ] ),
        ] );

        $token = $user->createToken( 'authToken' )->plainTextToken;

        return response()->json( [ 'user' => $user, 'token' => $token ] );
    }

    public function authenticate( Request $request ) {
        $credentials = $request->validate( [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ] );

        if ( auth()->attempt( $credentials ) ) {
            $token =  auth()->user();
            // ->createToken( 'authToken' )->plainTextToken;
            return response()->json( [ 'token' => $token ] );
        } else {
            return response()->json( [ 'error' => 'Unauthorized' ], 401 );
        }
    }

    // Add more methods for logout, password reset, etc.
}

