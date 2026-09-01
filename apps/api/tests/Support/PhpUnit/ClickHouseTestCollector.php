<?php

namespace Tests\Support\PhpUnit;

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use Tests\Support\ClickHouseTestResults;
use Throwable;

final class ClickHouseTestCollector
{
    private array $startedAt = [];

    private array $rows = [];

    public function __construct(
        private readonly string $runId,
    ) {
    }

    public function markStarted(Test $test): void
    {
        $this->startedAt[$test->id()] = hrtime(true);
    }

    public function record(Test $test, string $status, string $message = ''): void
    {
        $class = $test instanceof TestMethod ? $test->className() : $test->id();
        $name = $test instanceof TestMethod ? $test->methodName() : $test->name();

        $this->rows[] = [
            'event_time' => gmdate('Y-m-d H:i:s'),
            'run_id' => $this->runId,
            'suite' => ClickHouseTestResults::suiteOf($class),
            'test_class' => $class,
            'test_name' => $name,
            'status' => $status,
            'duration_ms' => $this->durationMs($test->id()),
            'message' => substr(trim($message), 0, 500),
        ];
    }

    public function pendingRows(): array
    {
        return $this->rows;
    }

    public function flush(): bool
    {
        $rows = $this->rows;
        $this->rows = [];
        $this->startedAt = [];

        if ($rows === []) {
            return true;
        }

        try {
            return ClickHouseTestResults::insertRows($rows);
        } catch (Throwable) {
            return false;
        }
    }

    private function durationMs(string $testId): float
    {
        $started = $this->startedAt[$testId] ?? hrtime(true);
        unset($this->startedAt[$testId]);

        return max(0, (hrtime(true) - $started) / 1_000_000);
    }
}
