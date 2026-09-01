<?php

namespace App\Services\Health\Checkers;

use App\Contracts\HealthCheckerInterface;
use App\Services\Health\HealthCheckResult;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PostgresHealthChecker implements HealthCheckerInterface
{
    public function name(): string
    {
        return 'postgres';
    }

    public function check(): HealthCheckResult
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1');

            return HealthCheckResult::ok($this->name());
        } catch (Throwable $exception) {
            return HealthCheckResult::fail($this->name(), $exception->getMessage());
        }
    }
}
