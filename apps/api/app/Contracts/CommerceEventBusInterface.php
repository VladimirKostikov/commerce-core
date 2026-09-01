<?php

namespace App\Contracts;

use App\Dto\CommerceLog;

interface CommerceEventBusInterface
{
    public function publish(CommerceLog $log): void;

    public function pull(int $timeoutMs): ?CommerceLog;
}
