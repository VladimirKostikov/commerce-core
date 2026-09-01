<?php

namespace Tests\Support;

final class ClickHouseMetrics
{
    public static function recordHttp(
        string $path,
        int $status,
        float $durationMs,
        ?string $eventTime = null,
    ): bool {
        $timeSql = $eventTime === null
            ? 'now()'
            : sprintf("toDateTime('%s')", addslashes($eventTime));

        $sql = sprintf(
            "INSERT INTO logs.http_metrics (event_time, path, status, duration_ms) VALUES (%s, '%s', %d, %F)",
            $timeSql,
            addslashes($path),
            $status,
            $durationMs,
        );

        $response = HttpProbe::post(InfrastructureHost::clickHouseUrl().'/', $sql);

        return $response['status'] === 200;
    }
}
