<?php

namespace Tests\Unit\Support;

use App\Support\NonNegativeMoney;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NonNegativeMoneyTest extends TestCase
{
    public function test_accepts_zero_and_positive(): void
    {
        $this->assertSame(0, NonNegativeMoney::of(0));
        $this->assertSame(500, NonNegativeMoney::of(500));
    }

    public function test_rejects_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        NonNegativeMoney::of(-1);
    }
}
