<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource {
    /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */

    public function toArray( Request $request ): array {
        return [
            'user' => new UserResource( $request ),
            'account' => new AccountResource( $this->account ),
            'transactions' => [
                'sent' =>  new TransactionResource( $this->sentTransactions ),
                'received' =>  new TransactionResource( $this->receivedTransactions )
            ],
            // 'posts' => new PostCollection( $this->posts ),
            'activities'=> new ActivityCollection( $this->activities ),
            'followers' => new FollowerCollection( $this->followers ),
            'following' => new FollowerCollection( $this->followings )
        ];
    }
}
