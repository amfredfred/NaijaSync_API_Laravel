<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Models\Like;

class Posts extends Model {
    use HasFactory, Searchable;

    protected $fillable = [
        'account_id',
        'title',
        'description',
        'file_url',
        'thumbnail_url',
        'views',
        'downloads',
        'likes',
        'duration',
        'mime_type',
        'source_qualities',
        'location_view',
        'location_download',
        'tags',
        'ratings',
        'price',
        'downloadable',
        'playtime',
        'file_type',
        'post_genre',
        'puid',
        'post_type'
    ];

    protected $casts = [
        'tags' => 'array',
        'source_qualities' => 'array',
        'post_genre' =>'array',
        'ratings' => 'array'
    ];

    // protected $appends = [ 's3_file_url', 's3_thumbnail_url' ];

    public function account() {
        return $this->belongsTo( Account::class );
    }

    public function likes() {
        return $this->hasMany( Like::class );
    }

    public function likedByAccounts() {
        return $this->belongsToMany( Account::class, 'likes', 'post_id', 'account_id' )->withTimestamps();
    }
}