<?php

namespace Tests\Unit\Services\Messaging;

use App\Dto\CommerceLog;
use App\Dto\CommerceWork;
use App\Services\Messaging\CommerceMessageProcessor;
use Tests\Fakes\RecordingCommerceEventBus;
use Tests\Fakes\RecordingCommerceInbox;
use Tests\Fakes\RecordingCommerceWorkQueue;
use Tests\TestCase;

final class CommerceMessageProcessorTest extends TestCase
{
    public function test_consume_events_projects_into_inbox(): void
    {
        $bus = new RecordingCommerceEventBus();
        $bus->published[] = new CommerceLog('payment', 'accepted', 'ord_e', 'evt_e', 'paid', 'ok');
        $inbox = new RecordingCommerceInbox();

        $handled = (new CommerceMessageProcessor(
            $bus,
            new RecordingCommerceWorkQueue(),
            $inbox,
        ))->consumeEvents(5, 10);

        $this->assertSame(1, $handled);
        $this->assertSame('ord_e', $inbox->events[0]->orderId);
        $this->assertSame(0, (new CommerceMessageProcessor(
            $bus,
            new RecordingCommerceWorkQueue(),
            $inbox,
        ))->consumeEvents(5, 10));
    }

    public function test_consume_notices_projects_into_inbox(): void
    {
        $queue = new RecordingCommerceWorkQueue();
        $queue->items[] = new CommerceWork('ord_n', 'issued');
        $inbox = new RecordingCommerceInbox();

        $handled = (new CommerceMessageProcessor(
            new RecordingCommerceEventBus(),
            $queue,
            $inbox,
        ))->consumeNotices(5);

        $this->assertSame(1, $handled);
        $this->assertSame(['ord_n', 'issued'], $inbox->notices[0]);
    }
}
