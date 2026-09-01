<?php

namespace App\Services\Logging;

use App\Contracts\CommerceEventBusInterface;
use App\Contracts\CommerceLoggerInterface;
use App\Dto\CommerceLog;

final class FanOutCommerceLogger implements CommerceLoggerInterface
{
    public function __construct(
        private readonly CommerceLoggerInterface $durable,
        private readonly CommerceEventBusInterface $events,
    ) {
    }

    public function record(CommerceLog $log): void
    {
        $this->durable->record($log);
        $this->events->publish($log);
    }
}
