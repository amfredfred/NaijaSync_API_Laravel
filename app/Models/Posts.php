<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Posts extends Model {
    use HasFactory, Searchable;

    protected $fillable = [
        'owner_id', 'title', 'description', 'file_url', 'thumbnail_url', 'views', 'downloads', 'likes',
        'duration', 'mime_type', 'source_qualities', 'location_view', 'location_download', 'tags',
        'ratings', 'price', 'downloadable', 'playtime',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    protected $appends = [ 's3_file_url', 's3_thumbnail_url' ];

    public function owner() {
        return $this->belongsTo( User::class, 'owner_id' );
    }

}