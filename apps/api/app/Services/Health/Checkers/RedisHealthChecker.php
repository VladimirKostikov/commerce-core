<?php

namespace App\Services\Health\Checkers;

use App\Contracts\HealthCheckerInterface;
use App\Services\Health\HealthCheckResult;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class RedisHealthChecker implements HealthCheckerInterface
{
    public function name(): string
    {
        return 'redis';
    }

    public function check(): HealthCheckResult
    {
        try {
            $pong = Redis::connection()->ping();

            if ($pong === true || $pong === 'PONG' || $pong === '+PONG') {
                return HealthCheckResult::ok($this->name());
            }

            return HealthCheckResult::fail($this->name(), 'Unexpected ping response');
        } catch (Throwable $exception) {
            return HealthCheckResult::fail($this->name(), $exception->getMessage());
        }
    }
}
