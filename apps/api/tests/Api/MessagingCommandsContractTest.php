<?php

namespace Tests\Api;

use Tests\TestCase;

final class MessagingCommandsContractTest extends TestCase
{
    public function test_consume_events_prints_integer_count(): void
    {
        $this->artisan('commerce:consume-events', ['--max' => 1, '--timeout' => 50])
            ->expectsOutput('0')
            ->assertSuccessful();
    }

    public function test_consume_notices_prints_integer_count(): void
    {
        $this->artisan('commerce:consume-notices', ['--max' => 1])
            ->expectsOutput('0')
            ->assertSuccessful();
    }
}
