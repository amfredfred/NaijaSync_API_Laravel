<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource {
    /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */

    public function toArray( Request $request ): array {
        return [
            'id'=>$this->id,
            'userId'=>$this->user_id,
            'points'=>$this->points,
            'bankAccountBalance'=>$this->bank_account_balance,
            'profilePics'=>$this->profile_pics,
            'bio'=>$this->bio,
            'gender'=>$this->gender,
            'profileCoverPics'=>$this->profile_cover_pics,
            'username'=>$this->username,
            'fullName' => $this->user->name,
            'following' => $this->followings,
            'followers' => $this->followers,
        ];
    }
}