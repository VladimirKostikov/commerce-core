<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\ClickHouseMetrics;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class HttpMetricsVolumeTest extends TestCase
{
    public function test_volume_of_http_metrics_can_be_stored(): void
    {
        $stored = 0;

        for ($i = 0; $i < 40; $i++) {
            $eventTime = gmdate('Y-m-d H:i:s', time() - ((40 - $i) * 30));
            $path = $i % 2 === 0 ? '/up' : '/api/health';
            $status = $i % 17 === 0 ? 503 : 200;
            $durationMs = 8.0 + ($i % 9);

            if (ClickHouseMetrics::recordHttp($path, $status, $durationMs, $eventTime)) {
                $stored++;
            }
        }

        $this->assertSame(40, $stored);

        $countSql = 'SELECT count() FROM logs.http_metrics WHERE event_time >= now() - INTERVAL 1 HOUR';
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.rawurlencode($countSql));

        $this->assertSame(200, $response['status']);
        $this->assertGreaterThanOrEqual(40, (int) trim($response['body']));
    }
}
