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
        'bank_account_balance',
    ];

    function user() {
        return  $this->belongsTo( User::class );
    }

}
