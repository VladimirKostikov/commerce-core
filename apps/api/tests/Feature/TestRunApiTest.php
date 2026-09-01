<?php

namespace Tests\Feature;

use App\Contracts\TestSuiteRunnerInterface;
use App\Dto\TestRunResult;
use App\Contracts\TestCatalogSyncInterface;
use Tests\TestCase;

final class TestRunApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(TestCatalogSyncInterface::class, function ($mock): void {
            $mock->shouldReceive('sync')->andReturn(0);
        });
    }

    public function test_run_suite_returns_output(): void
    {
        $this->mock(TestSuiteRunnerInterface::class, function ($mock): void {
            $mock->shouldReceive('run')->once()->with('Unit')->andReturn(
                new TestRunResult(true, 'Unit', 'OK (3 tests)'),
            );
        });

        $this->get('/api/tests/run?suite=Unit')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Unit  ok', false)
            ->assertSee('OK (3 tests)', false);
    }

    public function test_run_case_returns_output(): void
    {
        $id = 'Tests\\Feature\\HomePageTest::test_home_page_renders';

        $this->mock(TestSuiteRunnerInterface::class, function ($mock) use ($id): void {
            $mock->shouldReceive('runCase')->once()->with($id)->andReturn(
                new TestRunResult(true, $id, 'OK (1 test)'),
            );
        });

        $this->get('/api/tests/run?class=Tests\\Feature\\HomePageTest&method=test_home_page_renders')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee($id.'  ok', false);
    }

    public function test_rejects_unknown_suite(): void
    {
        $this->getJson('/api/tests/run?suite=Nope')->assertStatus(422);
    }

    public function test_log_empty(): void
    {
        $this->get('/api/tests/log?case=missing')->assertOk()->assertSee('пусто', false);
    }
}
