<?php

namespace Tests\Unit\Services\Testing;

use App\Services\Testing\ArtisanTestSuiteRunner;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use Tests\TestCase;

final class ArtisanTestSuiteRunnerTest extends TestCase
{
    public function test_runs_named_suite(): void
    {
        Process::fake([
            '*' => Process::result(output: 'OK', exitCode: 0),
        ]);

        $result = (new ArtisanTestSuiteRunner())->run('Unit');

        $this->assertTrue($result->ok);
        Process::assertRan(function ($process): bool {
            $line = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;

            return str_contains($line, '--testsuite=Unit')
                && str_contains($line, '--colors=never')
                && ! str_contains(strtolower($line), 'fpm')
                && str_contains($line, 'artisan test');
        });
    }

    public function test_runs_case(): void
    {
        Process::fake([
            '*' => Process::result(output: 'OK', exitCode: 0),
        ]);

        $id = 'Tests\\Feature\\HomePageTest::test_home_page_renders';
        $this->assertTrue((new ArtisanTestSuiteRunner())->runCase($id)->ok);
    }

    public function test_rejects_unknown_suite(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ArtisanTestSuiteRunner())->run('Nope');
    }
}
