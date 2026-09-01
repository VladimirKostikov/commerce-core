<?php

namespace Tests\Feature;

use App\Contracts\HealthServiceInterface;
use App\Services\Health\HealthCheckResult;
use App\Services\Health\HealthReport;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_ok_payload(): void
    {
        $this->mock(HealthServiceInterface::class, function ($mock): void {
            $mock->shouldReceive('report')->once()->andReturn(
                new HealthReport([
                    HealthCheckResult::ok('postgres'),
                    HealthCheckResult::ok('redis'),
                ])
            );
        });

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.0.name', 'postgres')
            ->assertJsonPath('checks.1.name', 'redis');
    }

    public function test_health_endpoint_returns_service_unavailable_when_unhealthy(): void
    {
        $this->mock(HealthServiceInterface::class, function ($mock): void {
            $mock->shouldReceive('report')->once()->andReturn(
                new HealthReport([
                    HealthCheckResult::fail('redis', 'down'),
                ])
            );
        });

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('checks.0.message', 'down');
    }
}
