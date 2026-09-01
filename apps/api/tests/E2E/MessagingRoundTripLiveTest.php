<?php

namespace Tests\E2E;

use App\Dto\CommerceLog;
use App\Dto\CommerceWork;
use App\Services\Messaging\KafkaCommerceEventBus;
use App\Services\Messaging\RabbitMqCommerceWorkQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LiveMessaging;
use Tests\TestCase;

final class MessagingRoundTripLiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_kafka_and_rabbitmq_round_trip_together(): void
    {
        config([
            'messaging.kafka.topic' => LiveMessaging::topic(),
            'messaging.kafka.group' => LiveMessaging::group(),
            'messaging.rabbitmq.queue' => LiveMessaging::queue(),
        ]);

        $orderId = 'ord_e2e_'.uniqid();
        $this->app->make(KafkaCommerceEventBus::class)->publish(new CommerceLog(
            'delivery',
            'issued',
            $orderId,
            'req_e2e',
            'delivered',
            'ok',
        ));
        $this->app->make(RabbitMqCommerceWorkQueue::class)->push(new CommerceWork($orderId, 'issued'));

        $event = $this->app->make(KafkaCommerceEventBus::class)->pull(12000);
        $notice = $this->app->make(RabbitMqCommerceWorkQueue::class)->pull();

        $this->assertNotNull($event);
        $this->assertSame($orderId, $event->orderId);
        $this->assertNotNull($notice);
        $this->assertSame('issued', $notice->kind);
    }
}
