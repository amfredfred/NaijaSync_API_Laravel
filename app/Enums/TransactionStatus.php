<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TransactionStatus extends Enum
{
    const Pending = 'pending';
    const Successful = 'successful';
    const Reverted = 'reverted';
    const Declined = 'declined';
}
