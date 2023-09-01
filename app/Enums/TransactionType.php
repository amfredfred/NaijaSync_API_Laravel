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
    const POINTS_EARNING  = 'POINTS_EARNING';
}