<?php

namespace App\Services\Logging;

use App\Contracts\ClickHouseClientInterface;
use App\Contracts\CommerceLoggerInterface;
use App\Dto\CommerceLog;
use Throwable;

final class ClickHouseCommerceLogger implements CommerceLoggerInterface
{
    public function __construct(
        private readonly ClickHouseClientInterface $clickHouse,
    ) {
    }

    public function record(CommerceLog $log): void
    {
        try {
            $this->clickHouse->insertJson(
                'logs.commerce_events',
                $log->toRow(now()->format('Y-m-d H:i:s')),
            );
        } catch (Throwable) {
        }
    }
}
