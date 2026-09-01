<?php

namespace App\Support;

use InvalidArgumentException;

final class NonNegativeMoney
{
    public static function of(int $amount): int
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        return $amount;
    }
}
