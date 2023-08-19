<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource {
    /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */

    public function toArray( Request $request ): array {

        // $this = parent::toArray( $request );

        $post = [
            'id'=>$this->id,
            'ownerId'=>$this->owner_id,
            'title'=>$this->title,
            'description'=>$this->description,
            'fileUrl'=>$this->file_url,
            'thumbnailUrl'=>$this->thumbnail_url,
            'views'=>$this->views,
            'downloads'=>$this->downloads,
            'likes'=>$this->likes,
            'duration'=>$this->duration,
            'mimeType'=>$this->mime_type,
            'sourceQualities'=>$this->source_qualities,
            'locationView'=>$this->location_view,
            'locationDownload'=>$this->location_download,
            'tags'=>$this->tags,
            'ratings'=>$this->tatings,
            'price'=>$this->price,
            'rewards'=>$this->rewards,
            'downloadable'=>$this->downloadable,
            'playtime'=>$this->playtime,
            'fileType'=>$this->file_type,
            'postSlug' => $this->post_slug,
            'updatedAt'=>$this->updated_at,
            'createdAt'=>$this->created_at,
        ];

        return  $post;
    }
}