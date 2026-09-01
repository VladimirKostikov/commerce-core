<?php

namespace App\Contracts;

use App\Dto\CommerceLog;

interface CommerceInboxInterface
{
    public function rememberNotice(string $orderId, string $kind): void;

    public function rememberEvent(CommerceLog $log): void;

    public function notices(): array;

    public function events(): array;

    public function lastEvent(): ?string;
}
