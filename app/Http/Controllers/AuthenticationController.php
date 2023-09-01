<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        Account::create( [
            'username' => Str::upper( uniqid( 'A-' ) ),
            'user_id' => $user->id,
        ] );

        $token = $user->createToken( 'authToken' )->plainTextToken;
        return response()->json( [ 'profile' => new ProfileResource( $user ), 'accessToken' => $token, 'message' => 'Welcome !!' ] );
    }

    public function authenticate( Request $request ) {
        $credentials = $request->validate( [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ] );

        if ( auth()->attempt( $credentials ) ) {
            $user = auth()->user();

            // Instead of using setRememberToken, you might generate an authentication token
            // Here, we're using Laravel's built-in Passport package to generate an access token
            $accessToken = $user->createToken( 'authToken' )->accessToken;

            return response()->json( [ 'access_token' => $accessToken ] );
        } else {
            return response()->json( [ 'error' => 'Unauthorized' ], 401 );
        }
    }
}