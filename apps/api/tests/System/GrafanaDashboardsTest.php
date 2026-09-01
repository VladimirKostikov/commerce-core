<?php

namespace Tests\System;

use PHPUnit\Framework\TestCase;
use Tests\Support\GrafanaProbe;

final class GrafanaDashboardsTest extends TestCase
{
    public function test_clickhouse_datasource_is_provisioned(): void
    {
        $response = GrafanaProbe::get('/api/datasources/uid/clickhouse');
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status']);
        $this->assertIsArray($payload);
        $this->assertSame('grafana-clickhouse-datasource', $payload['type'] ?? null);
        $this->assertSame('clickhouse', $payload['uid'] ?? null);
    }

    public function test_http_load_and_logs_dashboards_are_visible(): void
    {
        $response = GrafanaProbe::get('/api/search?type=dash-db');
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status']);
        $this->assertIsArray($payload);

        $uids = array_column($payload, 'uid');
        sort($uids);

        $this->assertContains('app-logs', $uids);
        $this->assertContains('http-load', $uids);
        $this->assertContains('commerce-events', $uids);
        $this->assertContains('phpunit-tests', $uids);
    }
}
