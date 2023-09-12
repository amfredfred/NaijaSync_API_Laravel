<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait CanMakeTransfer {
    public function sendPoints( $receiver, $amount ) {
        DB::transaction();
        try {
            if ( $this->points - $amount >= 0 ) {
                $this->points -= $amount;
                $receiver->posts += $amount;
                $this->save();
                $receiver->save();

                //Notify
                DB::commit();
                return true;
            } else {
                DB::rollBack();
                return false;
            }
        } catch ( \Throwable $th ) {
            DB::rollBack();
            //throw $th;
        }
    }
}