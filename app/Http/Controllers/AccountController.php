<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\PostCollection;
use App\Http\Resources\ProfileResource;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller {

    public function __construct() {
        return $this->middleware( 'auth:sanctum' )->only( [ 'updateAccount' ] );
    }

    public function posts( Request $request ) {
        try {
            $user = request()->user();
            $posts = $user->posts()->orderBy( 'created_at', 'desc' )->get();
            $posts = new PostCollection( $posts );
            return response()->json( $posts, 200 );
        } catch ( \Throwable $th ) {
            return response()->json( [ 'message' => $th->getMessage() ], 500 );
        }
    }

    public function checkAccountExists( Request $request ) {
        $account = null;
        if ( $request->has( 'username' ) ) {
            $identifier = $request->query( 'username' );
            $account = Account::where( 'username', $identifier )->first();
        } else if ( $request->has( 'email' ) ) {
            $identifier = $request->query( 'email' );
            $user = User::where( 'email', $identifier )->first();
            $account = $user->account;
        }
        if ( $account )
        return response()->json( [ 'exists' => true, 'account' => new AccountResource( $account ) ] );
        else
        return response()->json( [ 'exists' => false, 'account' => $account ] );
    }

    public function updateAccount( Request $request ) {
        $user = request()->user();
        if ( !$user ) {
            return response()->json( [ 'message' => 'User not found' ], 404 );
        }

        $account = $user->account;

        if ( $request->has( 'username' ) )
        $account->username = $request->input( 'username' );
        if ( $request->has( 'points' ) )
        $account->points += ( float ) $request->input( 'points' );
        if ( $request->has( 'gender' ) )
        $account->gender = $request->input( 'gender' );
        if ( $request->has( 'name' ) )
        $user->name = $request->input( 'name' );
        if ( $request->has( 'bio' ) )
        $account->bio = $request->input( 'bio' );
        if ( $request->has( 'followed' ) ) {
            $followed = $user->followings()->where( 'following_id', $request->input( 'userId' ) )->first();
            $accountToFollow = User::where( 'id', $request->input( 'userId' ) )->first();
            if ( $followed ) {
                if ( $accountToFollow )
                $user->followings()->detach( $accountToFollow->id );
                $message = 'Unliked';
            } else {
                if ( $accountToFollow )
                $user->followings()->attach( $accountToFollow->id );
                $message = 'Liked';
            }
        }

        if ( $request->hasFile( 'profile-image' ) ) {
            $profileImage = $request->file( 'profile-image' );
        }
        if ( $request->hasFile( 'cover-image' ) ) {
            $coverImage = $request->file( 'cover-image' );
        }

        if ( $request->has( 'new_password' ) && $request->has( 'current_password' ) ) {
            // Validate form input
            $request->validate( [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ] );
            if ( !Hash::check( $request->current_password, $user->password ) ) {
                return response()->json( [ 'message' => 'The current password is incorrect.' ], 422 );
            }
            $user->password = Hash::make( $request->new_password );
        }

        $user->save();
        $account->save();

        $token = $user->createToken( 'authToken' )->plainTextToken;
        return response()->json( [
            'profile' =>new ProfileResource( $user ),
            'accessToken' => $token,
            'message' => 'Your account has been updated'
        ] );

    }

    public function accountInfo( Request $requst ) {
        $user = $requst->user();
        if ( $user )
        return new AccountResource( $user );
    }
}
