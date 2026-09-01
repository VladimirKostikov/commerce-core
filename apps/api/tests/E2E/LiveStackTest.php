<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class LiveStackTest extends TestCase
{
    public function test_home_page_via_nginx(): void
    {
        $response = HttpProbe::get(InfrastructureHost::appUrl().'/');

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('Application is running.', $response['body']);
    }

    public function test_health_api_via_nginx(): void
    {
        $response = HttpProbe::get(InfrastructureHost::appUrl().'/api/health');
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status']);
        $this->assertIsArray($payload);
        $this->assertSame('ok', $payload['status']);

        $names = array_column($payload['checks'], 'name');
        sort($names);

        $this->assertSame([
            'clickhouse',
            'kafka',
            'postgres',
            'rabbitmq',
            'redis',
        ], $names);

        foreach ($payload['checks'] as $check) {
            $this->assertTrue($check['ok'], $check['name'].' is unhealthy');
        }
    }

    public function test_framework_up_endpoint_via_nginx(): void
    {
        $response = HttpProbe::get(InfrastructureHost::appUrl().'/up');

        $this->assertSame(200, $response['status']);
    }
}
