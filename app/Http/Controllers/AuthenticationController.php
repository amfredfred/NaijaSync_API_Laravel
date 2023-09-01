<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        try {
            $credentials = $request->validate( [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ] );

            $user  = auth()->attempt( $credentials );

            $user = User::where( 'email', $credentials[ 'email' ] )->first();

            if ( !$user ) {
                return response()->json( [ 'message' => 'Account with email '. Str::limit( $credentials[ 'email' ], 10, '...' ) .' does`nt exist' ], 404 );
            }
            if ( !Hash::check( $credentials[ 'password' ], $user->password ) ) {
                return response()->json( [ 'message' => 'Incorrect account`s email/password.' ], 401 );
            }

            $token = $user->createToken( 'authToken' )->plainTextToken;
            return response()->json( [ 'profile' => new ProfileResource( $user ), 'accessToken' => $token, 'message' => 'Welcome back '.$user->account->username.'  !!' ] );

        } catch ( \Throwable $th ) {
            return response()->json( [ 'message' => $th->getMessage() ], 500 );
        }
    }
}