<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'from_account_id',
        'to_account_id',
        'amount',
        'description',
        'recipient_name',
        'transaction_reference',
        'bank_name',
        'bank_account_number',
        'status',
    ];

    public function sender() {
        return $this->belongsTo( User::class, 'from_account_id' );
    }

    public function receiver() {
        return $this->belongsTo( User::class, 'to_account_id' );
    }
}
