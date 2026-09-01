<?php

namespace Tests\Fakes;

use App\Contracts\CommerceWorkQueueInterface;
use App\Dto\CommerceWork;

final class RecordingCommerceWorkQueue implements CommerceWorkQueueInterface
{
    public array $items = [];

    public function push(CommerceWork $work): void
    {
        $this->items[] = $work;
    }

    public function pull(): ?CommerceWork
    {
        return array_shift($this->items);
    }
}
