<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceInboxInterface;
use App\Dto\CommerceLog;

final class NullCommerceInbox implements CommerceInboxInterface
{
    public function rememberNotice(string $orderId, string $kind): void
    {
    }

    public function rememberEvent(CommerceLog $log): void
    {
    }

    public function notices(): array
    {
        return [];
    }

    public function events(): array
    {
        return [];
    }

    public function lastEvent(): ?string
    {
        return null;
    }
}
