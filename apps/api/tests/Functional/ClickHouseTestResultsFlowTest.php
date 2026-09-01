<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use Tests\Support\ClickHouseTestResults;

final class ClickHouseTestResultsFlowTest extends TestCase
{
    public function test_row_can_be_stored_and_read(): void
    {
        $runId = 'flow_'.bin2hex(random_bytes(4));

        $this->assertTrue(ClickHouseTestResults::insertRows([[
            'event_time' => gmdate('Y-m-d H:i:s'),
            'run_id' => $runId,
            'suite' => 'Functional',
            'test_class' => 'Tests\\Functional\\ClickHouseTestResultsFlowTest',
            'test_name' => 'test_row_can_be_stored_and_read',
            'status' => 'passed',
            'duration_ms' => 1.5,
            'message' => '',
        ]]));

        $this->assertSame(1, ClickHouseTestResults::countWhere("run_id = '".$runId."'"));
    }
}
