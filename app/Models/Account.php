<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Account extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'username',
        'profile_pics',
        'bio',
        'gender',
        'profile_cover_pics',
        'bank_account_balance',
    ];

    protected $casts = [
        'profile_pics'=> 'array',
        'profile_cover_pics'=>'array'
    ];

    function user() {
        return  $this->belongsTo( User::class );
    }

}
