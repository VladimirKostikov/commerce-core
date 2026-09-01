<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceEventBusInterface;
use App\Dto\CommerceLog;

final class NullCommerceEventBus implements CommerceEventBusInterface
{
    public function publish(CommerceLog $log): void
    {
    }

    public function pull(int $timeoutMs): ?CommerceLog
    {
        return null;
    }
}
