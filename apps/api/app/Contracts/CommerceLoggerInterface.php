<?php

namespace App\Contracts;

use App\Dto\CommerceLog;

interface CommerceLoggerInterface
{
    public function record(CommerceLog $log): void;
}
