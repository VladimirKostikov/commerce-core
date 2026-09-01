<?php

namespace App\Services\Testing;

use App\Contracts\TestRunLogStoreInterface;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class RedisTestRunLogStore implements TestRunLogStoreInterface
{
    public function put(string $id, string $output): void
    {
        try {
            Redis::hset($this->key(), $id, $output);
        } catch (Throwable) {
        }
    }

    public function get(string $id): ?string
    {
        try {
            $value = Redis::hget($this->key(), $id);
        } catch (Throwable) {
            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function key(): string
    {
        return (string) config('test_runs.logs_key', 'test:run:logs');
    }
}
