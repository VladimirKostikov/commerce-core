<?php

namespace App\Services\Health\Checkers;

use App\Contracts\HealthCheckerInterface;
use App\Services\Health\HealthCheckResult;
use Illuminate\Support\Facades\Http;
use Throwable;

final class ClickHouseHealthChecker implements HealthCheckerInterface
{
    public function name(): string
    {
        return 'clickhouse';
    }

    public function check(): HealthCheckResult
    {
        $scheme = (string) config('health.clickhouse.scheme');
        $host = (string) config('health.clickhouse.host');
        $port = (int) config('health.clickhouse.port');
        $timeout = (float) config('health.timeout');
        $url = sprintf('%s://%s:%d/ping', $scheme, $host, $port);

        try {
            $response = Http::timeout($timeout)->get($url);

            if ($response->successful()) {
                return HealthCheckResult::ok($this->name());
            }

            return HealthCheckResult::fail($this->name(), 'HTTP '.$response->status());
        } catch (Throwable $exception) {
            return HealthCheckResult::fail($this->name(), $exception->getMessage());
        }
    }
}
