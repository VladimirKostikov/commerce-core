<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceInboxInterface;
use App\Dto\CommerceLog;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class RedisCommerceInbox implements CommerceInboxInterface
{
    public function rememberNotice(string $orderId, string $kind): void
    {
        try {
            $this->push($this->noticesKey(), [
                'order_id' => $orderId,
                'kind' => $kind,
            ]);
        } catch (Throwable) {
        }
    }

    public function rememberEvent(CommerceLog $log): void
    {
        try {
            Redis::set($this->lastEventKey(), $log->orderId.':'.$log->event);
            $this->push($this->eventsKey(), [
                'order_id' => $log->orderId,
                'event' => $log->event,
                'channel' => $log->channel,
            ]);
        } catch (Throwable) {
        }
    }

    public function notices(): array
    {
        return $this->rows($this->noticesKey());
    }

    public function events(): array
    {
        return $this->rows($this->eventsKey());
    }

    public function lastEvent(): ?string
    {
        try {
            $value = Redis::get($this->lastEventKey());
        } catch (Throwable) {
            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function push(string $key, array $payload): void
    {
        Redis::lpush($key, json_encode($payload, JSON_THROW_ON_ERROR));
        Redis::ltrim($key, 0, 99);
    }

    private function rows(string $key): array
    {
        try {
            $items = Redis::lrange($key, 0, 99);
        } catch (Throwable) {
            return [];
        }

        if (! is_array($items)) {
            return [];
        }

        $rows = [];
        foreach ($items as $item) {
            $decoded = $this->decode($item);
            if ($decoded !== null) {
                $rows[] = $decoded;
            }
        }

        return $rows;
    }

    private function decode(mixed $item): ?array
    {
        if (! is_string($item) || $item === '') {
            return null;
        }

        try {
            $row = json_decode($item, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($row) ? $row : null;
    }

    private function noticesKey(): string
    {
        return (string) config('messaging.redis.notices_key', 'commerce:notices');
    }

    private function eventsKey(): string
    {
        return (string) config('messaging.redis.events_key', 'commerce:events:inbox');
    }

    private function lastEventKey(): string
    {
        return (string) config('messaging.redis.last_event_key', 'commerce:events:last');
    }
}
