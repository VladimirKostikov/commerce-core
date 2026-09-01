<?php

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use Tests\Support\ClickHouseTestResults;

final class ClickHouseTestResultsUniquenessTest extends TestCase
{
    public function test_same_test_name_can_be_stored_twice_as_history(): void
    {
        $runA = 'hist_a_'.bin2hex(random_bytes(3));
        $runB = 'hist_b_'.bin2hex(random_bytes(3));
        $row = static fn (string $runId): array => [
            'event_time' => gmdate('Y-m-d H:i:s'),
            'run_id' => $runId,
            'suite' => 'Database',
            'test_class' => 'Tests\\Database\\ClickHouseTestResultsUniquenessTest',
            'test_name' => 'test_same_test_name_can_be_stored_twice_as_history',
            'status' => 'passed',
            'duration_ms' => 0.2,
            'message' => '',
        ];

        $this->assertTrue(ClickHouseTestResults::insertRows([$row($runA), $row($runB)]));
        $this->assertSame(
            2,
            ClickHouseTestResults::countWhere("test_name = 'test_same_test_name_can_be_stored_twice_as_history' AND run_id IN ('".$runA."','".$runB."')"),
        );
    }
}
