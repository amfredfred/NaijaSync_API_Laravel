<?php declare( strict_types = 1 );

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
* @method static static OptionOne()
* @method static static OptionTwo()
* @method static static OptionThree()
*/
final class TransactionType extends Enum {
    const SENDING = 'SENDING';
    const RECEIVING = 'RECEIVING';
    const REQUESTING = 'REQUESTING';
    const POINTS_EARNED  = 'POINTS_EARNED';
    const TRANSFER = 'TRANSFER';
    const AIRTIME = 'AIRTIME';
    const CONVERTTION = 'CONVERTTION';
    const DEPOSIT = 'DEPOSIT';
    const CASHOUT = 'CASHOUT';
    const INNTERNAL_TRANSFER = 'INNTERNAL_TRANSFER';
}