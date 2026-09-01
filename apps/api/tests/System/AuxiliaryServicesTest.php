<?php

namespace Tests\System;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class AuxiliaryServicesTest extends TestCase
{
    public function test_clickhouse_ping(): void
    {
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/ping');

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('Ok', $response['body']);
    }

    public function test_clickhouse_logs_table_exists(): void
    {
        $query = rawurlencode('EXISTS TABLE logs.app_logs');
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.$query);

        $this->assertSame(200, $response['status']);
        $this->assertSame('1', trim($response['body']));
    }

    public function test_clickhouse_http_metrics_table_exists(): void
    {
        $query = rawurlencode('EXISTS TABLE logs.http_metrics');
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.$query);

        $this->assertSame(200, $response['status']);
        $this->assertSame('1', trim($response['body']));
    }

    public function test_clickhouse_commerce_events_table_exists(): void
    {
        $query = rawurlencode('EXISTS TABLE logs.commerce_events');
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.$query);

        $this->assertSame(200, $response['status']);
        $this->assertSame('1', trim($response['body']));
    }

    public function test_clickhouse_test_results_table_exists(): void
    {
        $query = rawurlencode('EXISTS TABLE logs.test_results');
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.$query);

        $this->assertSame(200, $response['status']);
        $this->assertSame('1', trim($response['body']));
    }

    public function test_grafana_health(): void
    {
        $response = HttpProbe::get(InfrastructureHost::grafanaUrl().'/api/health', 10);

        $this->assertSame(200, $response['status']);

        $payload = json_decode($response['body'], true);

        $this->assertIsArray($payload);
        $this->assertContains($payload['database'] ?? null, ['ok', 'OK', true, 'up']);
    }

    public function test_rabbitmq_management_is_up(): void
    {
        $response = HttpProbe::get(InfrastructureHost::rabbitMqManagementUrl().'/', 5, []);

        $this->assertContains($response['status'], [200, 301, 302, 401, 403]);
        $this->assertNotSame(0, $response['status']);
    }
}
