<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller {
    public function sendFunds() {

    }

    public function updateTransaction() {

    }

    public function getTransationByReference() {

    }

    public function accountTransactions() {

    }

    public function transferPoints( $from, $to, $amount ) {
        $acc1 = $from;
        $acc2 = $to;
        if ( $acc1->points - $amount >= 0 ) {
            $acc2->posts += $amount;
            $acc1->points -= $amount;
            $acc1->save();
            $acc2->save();
            // return true;
        } else {
            // return false;
        }
    }
}