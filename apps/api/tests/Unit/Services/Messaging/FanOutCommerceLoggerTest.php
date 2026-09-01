<?php

namespace Tests\Unit\Services\Messaging;

use App\Dto\CommerceLog;
use App\Services\Logging\FanOutCommerceLogger;
use Tests\Fakes\RecordingCommerceEventBus;
use Tests\Fakes\RecordingCommerceLogger;
use Tests\TestCase;

final class FanOutCommerceLoggerTest extends TestCase
{
    public function test_record_writes_durable_log_and_publishes_event(): void
    {
        $durable = new RecordingCommerceLogger();
        $bus = new RecordingCommerceEventBus();
        $log = new CommerceLog('delivery', 'issued', 'ord_2', 'req_2', 'delivered', 'ok');

        (new FanOutCommerceLogger($durable, $bus))->record($log);

        $this->assertSame([$log], $durable->logs);
        $this->assertSame([$log], $bus->published);
    }
}
