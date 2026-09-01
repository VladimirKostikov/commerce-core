<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceWorkQueueInterface;
use App\Dto\CommerceWork;

final class NullCommerceWorkQueue implements CommerceWorkQueueInterface
{
    public function push(CommerceWork $work): void
    {
    }

    public function pull(): ?CommerceWork
    {
        return null;
    }
}
