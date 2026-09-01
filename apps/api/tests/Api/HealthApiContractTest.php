<?php

namespace Tests\Api;

use App\Contracts\HealthServiceInterface;
use App\Services\Health\HealthCheckResult;
use App\Services\Health\HealthReport;
use Tests\TestCase;

final class HealthApiContractTest extends TestCase
{
    public function test_health_api_json_contract(): void
    {
        $this->mock(HealthServiceInterface::class, function ($mock): void {
            $mock->shouldReceive('report')->andReturn(
                new HealthReport([
                    HealthCheckResult::ok('postgres'),
                ])
            );
        });

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'checks' => [
                    '*' => ['name', 'ok', 'message'],
                ],
            ]);
    }
}
