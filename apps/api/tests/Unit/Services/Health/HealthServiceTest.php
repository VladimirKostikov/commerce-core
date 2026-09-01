<?php

namespace Tests\Unit\Services\Health;

use App\Services\Health\HealthReport;
use App\Services\Health\HealthService;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeHealthChecker;

final class HealthServiceTest extends TestCase
{
    public function test_report_is_healthy_when_all_checkers_pass(): void
    {
        $service = new HealthService([
            new FakeHealthChecker('postgres', true),
            new FakeHealthChecker('redis', true),
        ]);

        $report = $service->report();

        $this->assertTrue($report->isHealthy());
        $this->assertSame('ok', $report->toArray()['status']);
        $this->assertCount(2, $report->results());
    }

    public function test_report_is_unhealthy_when_any_checker_fails(): void
    {
        $service = new HealthService([
            new FakeHealthChecker('postgres', true),
            new FakeHealthChecker('redis', false, 'down'),
        ]);

        $report = $service->report();

        $this->assertFalse($report->isHealthy());
        $this->assertSame('error', $report->toArray()['status']);
        $this->assertSame('down', $report->results()[1]->message);
    }
}
