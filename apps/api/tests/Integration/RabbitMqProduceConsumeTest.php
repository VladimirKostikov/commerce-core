<?php

namespace Tests\Integration;

use App\Dto\CommerceWork;
use App\Services\Messaging\RabbitMqCommerceWorkQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LiveMessaging;
use Tests\TestCase;

final class RabbitMqProduceConsumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pushed_notice_can_be_pulled(): void
    {
        config([
            'messaging.rabbitmq.queue' => LiveMessaging::queue(),
        ]);

        $queue = $this->app->make(RabbitMqCommerceWorkQueue::class);
        $work = new CommerceWork('ord_rabbit_'.uniqid(), 'issued');

        $queue->push($work);
        $got = $queue->pull();

        $this->assertNotNull($got);
        $this->assertSame($work->orderId, $got->orderId);
        $this->assertSame('issued', $got->kind);
        $this->assertNull($queue->pull());
    }
}
