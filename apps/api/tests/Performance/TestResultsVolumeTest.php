<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\ClickHouseTestResults;

final class TestResultsVolumeTest extends TestCase
{
    public function test_volume_of_test_results_can_be_stored(): void
    {
        $runId = 'vol_'.bin2hex(random_bytes(4));
        $rows = [];

        for ($i = 0; $i < 80; $i++) {
            $rows[] = [
                'event_time' => gmdate('Y-m-d H:i:s'),
                'run_id' => $runId,
                'suite' => 'Performance',
                'test_class' => 'Tests\\Performance\\TestResultsVolumeTest',
                'test_name' => 'test_item_'.$i,
                'status' => 'passed',
                'duration_ms' => 1.0 + ($i % 9),
                'message' => '',
            ];
        }

        $this->assertTrue(ClickHouseTestResults::insertRows($rows));
        $this->assertSame(80, ClickHouseTestResults::countWhere("run_id = '".$runId."'"));
    }

    public function test_spike_of_suite_batches_succeed(): void
    {
        $ok = 0;

        for ($i = 0; $i < 20; $i++) {
            $runId = 'spike_'.bin2hex(random_bytes(3)).'_'.$i;
            if (ClickHouseTestResults::insertRows([[
                'event_time' => gmdate('Y-m-d H:i:s'),
                'run_id' => $runId,
                'suite' => 'Performance',
                'test_class' => 'Tests\\Performance\\TestResultsVolumeTest',
                'test_name' => 'spike_'.$i,
                'status' => 'passed',
                'duration_ms' => 0.5,
                'message' => '',
            ]])) {
                $ok++;
            }
        }

        $this->assertSame(20, $ok);
    }

    public function test_endurance_of_test_result_inserts_stays_bounded(): void
    {
        $startedAt = microtime(true);

        for ($i = 0; $i < 40; $i++) {
            $this->assertTrue(ClickHouseTestResults::insertRows([[
                'event_time' => gmdate('Y-m-d H:i:s'),
                'run_id' => 'end_'.bin2hex(random_bytes(3)),
                'suite' => 'Performance',
                'test_class' => 'Tests\\Performance\\TestResultsVolumeTest',
                'test_name' => 'endurance_'.$i,
                'status' => 'passed',
                'duration_ms' => 0.4,
                'message' => '',
            ]]));
        }

        $this->assertLessThan(20, microtime(true) - $startedAt);
    }
}
