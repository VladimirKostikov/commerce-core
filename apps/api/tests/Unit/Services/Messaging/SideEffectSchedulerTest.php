<?php

namespace Tests\Unit\Services\Messaging;

use App\Services\Messaging\SideEffectScheduler;
use Tests\TestCase;

final class SideEffectSchedulerTest extends TestCase
{
    public function test_sync_flag_runs_callback_immediately(): void
    {
        config(['messaging.sync_side_effects' => true]);
        $ran = false;

        (new SideEffectScheduler())->run(function () use (&$ran): void {
            $ran = true;
        });

        $this->assertTrue($ran);
    }
}
