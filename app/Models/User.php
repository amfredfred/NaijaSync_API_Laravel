<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Account;
use App\Models\Posts;
use App\Models\Activity;
use App\Models\Transaction;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
    * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
    * The attributes that should be cast.
    *
    * @var array<string, string>
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function account() {
        return $this->hasOne( Account::class );
    }

    public function posts() {
        return $this->hasMany( Posts::class, 'account_id' );
    }

    public function activities() {
        return $this->belongsToMany(Account::class, 'activities', 'account_id' );
    }

    public function sentTransactions() {
        return $this->hasMany( Transaction::class, 'sender_id' );
    }

    public function receivedTransactions() {
        return $this->hasMany( Transaction::class, 'receiver_id' );
    }

    public function followers() {
        return $this->belongsToMany( User::class, 'followers', 'following_id', 'follower_id' );
    }

    public function following() {
        return $this->belongsToMany( User::class, 'followers', 'follower_id', 'following_id' );
    }
}
