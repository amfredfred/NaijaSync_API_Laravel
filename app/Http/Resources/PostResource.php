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
            'puid'=>$this->puid,
            'id'=>$this->id,
            'ownerId'=>$this->owner_id,
            'title'=>$this->title,
            'description'=>$this->description,
            'fileUrl'=>$this->file_url,
            'thumbnailUrl'=>$this->thumbnail_url,
            'views'=>$this->views,
            'downloads'=>$this->downloads,
            'likes'=>$this->likes,
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
            'postType' => $this->post_type,
            'updatedAt'=>$this->updated_at,
            'createdAt'=>$this->created_at,
        ];

        return  $post;
    }
}