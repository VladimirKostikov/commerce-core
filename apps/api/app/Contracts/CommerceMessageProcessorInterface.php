<?php

namespace App\Contracts;

interface CommerceMessageProcessorInterface
{
    public function consumeEvents(int $max, int $timeoutMs): int;

    public function consumeNotices(int $max): int;
}
