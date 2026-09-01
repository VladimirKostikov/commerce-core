<?php

namespace Tests\Unit\Services\Health;

use App\Services\Health\HealthCheckResult;
use App\Services\Health\HealthReport;
use PHPUnit\Framework\TestCase;

final class HealthReportTest extends TestCase
{
    public function test_empty_report_is_healthy(): void
    {
        $report = new HealthReport([]);

        $this->assertTrue($report->isHealthy());
        $this->assertSame(['status' => 'ok', 'checks' => []], $report->toArray());
    }

    public function test_result_payload_shape(): void
    {
        $result = HealthCheckResult::fail('kafka', 'timeout');

        $this->assertSame([
            'name' => 'kafka',
            'ok' => false,
            'message' => 'timeout',
        ], $result->toArray());
    }
}
