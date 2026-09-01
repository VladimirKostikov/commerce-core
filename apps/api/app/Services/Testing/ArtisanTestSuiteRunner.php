<?php

namespace App\Services\Testing;

use App\Contracts\TestSuiteRunnerInterface;
use App\Dto\TestRunResult;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;

final class ArtisanTestSuiteRunner implements TestSuiteRunnerInterface
{
    public function run(string $suite): TestRunResult
    {
        if (! KnownTestSuites::contains($suite)) {
            throw new InvalidArgumentException('Unknown test suite');
        }

        $result = Process::path(base_path())
            ->timeout((int) config('test_runs.timeout', 300))
            ->run($this->suiteCommand($suite));

        return new TestRunResult(
            $result->successful(),
            $suite,
            trim($result->output()."\n".$result->errorOutput()),
        );
    }

    public function runCase(string $id): TestRunResult
    {
        if (! str_contains($id, '::')) {
            throw new InvalidArgumentException('Unknown test case');
        }

        $result = Process::path(base_path())
            ->timeout((int) config('test_runs.timeout', 300))
            ->run($this->testCommand(['--filter=/^'.preg_quote($id, '/').'$/']));

        return new TestRunResult(
            $result->successful(),
            $id,
            trim($result->output()."\n".$result->errorOutput()),
        );
    }

    private function suiteCommand(string $suite): array
    {
        if ($suite === KnownTestSuites::ALL) {
            return $this->testCommand([]);
        }

        return $this->testCommand(['--testsuite='.$suite]);
    }

    private function testCommand(array $extra): array
    {
        return [PhpCliBinary::path(), 'artisan', 'test', '--colors=never', ...$extra];
    }
}
