<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class ClickHouseVolumeTest extends TestCase
{
    public function test_clickhouse_accepts_volume_inserts_and_reads(): void
    {
        $base = InfrastructureHost::clickHouseUrl();
        $batch = uniqid('volume_', true);

        for ($i = 0; $i < 25; $i++) {
            $sql = sprintf(
                "INSERT INTO logs.app_logs (level, channel, message, context) VALUES ('info', 'volume', '%s', '{}')",
                $batch
            );
            $response = HttpProbe::post($base.'/', $sql);
            $this->assertSame(200, $response['status']);
        }

        $countSql = sprintf(
            "SELECT count() FROM logs.app_logs WHERE message = '%s'",
            $batch
        );
        $response = HttpProbe::get($base.'/?query='.rawurlencode($countSql));

        $this->assertSame(200, $response['status']);
        $this->assertSame('25', trim($response['body']));
    }
}
