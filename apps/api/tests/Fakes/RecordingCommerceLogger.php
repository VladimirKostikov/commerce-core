<?php

namespace Tests\Fakes;

use App\Contracts\CommerceLoggerInterface;
use App\Dto\CommerceLog;

final class RecordingCommerceLogger implements CommerceLoggerInterface
{
    public array $logs = [];

    public function record(CommerceLog $log): void
    {
        $this->logs[] = $log;
    }
}
