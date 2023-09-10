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

        $quotes = [
            'The only way to do great work is to love what you do. - Steve Jobs',
            'Innovation distinguishes between a leader and a follower. - Steve Jobs',
            'The best time to plant a tree was 20 years ago. The second best time is now. - Chinese Proverb',
            "Don't watch the clock; do what it does. Keep going. - Sam Levenson",
            'Success is not final, failure is not fatal: It is the courage to continue that counts. - Winston Churchill',
        ];

        $randomQuote = $quotes[ array_rand( $quotes ) ];

        $user = User::create( [
            'name' => $validatedData[ 'name' ],
            'email' => $validatedData[ 'email' ],
            'password' => Hash::make( $validatedData[ 'password' ] ),
        ] );

        Account::create( [
            'username' => Str::random( length:10 ),
            'user_id' => $user->id,
            'profile_pics' => [ fake()->imageUrl() ],
            'profile_cover_pics' => [ fake()->imageUrl() ],
            'bio' => $randomQuote
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