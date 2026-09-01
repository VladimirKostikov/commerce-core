<?php

namespace Tests\Fakes;

use App\Contracts\CommerceEventBusInterface;
use App\Dto\CommerceLog;

final class RecordingCommerceEventBus implements CommerceEventBusInterface
{
    public array $published = [];

    public function publish(CommerceLog $log): void
    {
        $this->published[] = $log;
    }

    public function pull(int $timeoutMs): ?CommerceLog
    {
        return array_shift($this->published);
    }
}
