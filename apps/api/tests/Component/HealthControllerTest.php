<?php

namespace Tests\Component;

use App\Contracts\HealthServiceInterface;
use App\Http\Controllers\HealthController;
use App\Services\Health\HealthCheckResult;
use App\Services\Health\HealthReport;
use Tests\TestCase;

final class HealthControllerTest extends TestCase
{
    public function test_controller_returns_json_from_service(): void
    {
        $service = $this->createMock(HealthServiceInterface::class);
        $service->method('report')->willReturn(
            new HealthReport([
                HealthCheckResult::ok('postgres'),
            ])
        );

        $controller = new HealthController();
        $response = $controller($service);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getData(true)['status']);
    }

    public function test_controller_returns_503_when_service_is_unhealthy(): void
    {
        $service = $this->createMock(HealthServiceInterface::class);
        $service->method('report')->willReturn(
            new HealthReport([
                HealthCheckResult::fail('postgres', 'timeout'),
            ])
        );

        $controller = new HealthController();
        $response = $controller($service);

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('error', $response->getData(true)['status']);
    }
}
