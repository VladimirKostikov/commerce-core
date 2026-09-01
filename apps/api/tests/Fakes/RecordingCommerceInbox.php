<?php

namespace Tests\Fakes;

use App\Contracts\CommerceInboxInterface;
use App\Dto\CommerceLog;

final class RecordingCommerceInbox implements CommerceInboxInterface
{
    public array $notices = [];

    public array $events = [];

    public function rememberNotice(string $orderId, string $kind): void
    {
        $this->notices[] = [$orderId, $kind];
    }

    public function rememberEvent(CommerceLog $log): void
    {
        $this->events[] = $log;
    }

    public function notices(): array
    {
        return array_map(fn (array $row): array => [
            'order_id' => $row[0],
            'kind' => $row[1],
        ], $this->notices);
    }

    public function events(): array
    {
        return array_map(fn (CommerceLog $log): array => [
            'order_id' => $log->orderId,
            'event' => $log->event,
            'channel' => $log->channel,
        ], $this->events);
    }

    public function lastEvent(): ?string
    {
        $log = $this->events === [] ? null : $this->events[array_key_last($this->events)];

        return $log instanceof CommerceLog ? $log->orderId.':'.$log->event : null;
    }
}
