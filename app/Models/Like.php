<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model {
    use HasFactory;

    protected $fillable = [ 'account_id', 'post_id' ];

    public function post() {
        return $this->belongsTo( Posts::class );
    }

    public function account() {
        return $this->belongsTo( Account::class );
    }
}
