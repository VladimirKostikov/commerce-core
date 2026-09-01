<?php

namespace App\Contracts;

use App\Dto\CommerceWork;

interface CommerceWorkQueueInterface
{
    public function push(CommerceWork $work): void;

    public function pull(): ?CommerceWork;
}
