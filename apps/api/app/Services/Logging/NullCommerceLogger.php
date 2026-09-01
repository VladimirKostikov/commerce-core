<?php

namespace App\Services\Logging;

use App\Contracts\CommerceLoggerInterface;
use App\Dto\CommerceLog;

final class NullCommerceLogger implements CommerceLoggerInterface
{
    public function record(CommerceLog $log): void
    {
    }
}
