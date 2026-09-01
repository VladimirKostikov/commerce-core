<?php

namespace Tests\Feature;

use Tests\TestCase;

final class MessagingConsumeCommandsTest extends TestCase
{
    public function test_consume_events_command_succeeds_with_empty_bus(): void
    {
        $this->artisan('commerce:consume-events', ['--max' => 3, '--timeout' => 50])
            ->expectsOutput('0')
            ->assertSuccessful();
    }

    public function test_consume_notices_command_succeeds_with_empty_queue(): void
    {
        $this->artisan('commerce:consume-notices', ['--max' => 3])
            ->expectsOutput('0')
            ->assertSuccessful();
    }
}
