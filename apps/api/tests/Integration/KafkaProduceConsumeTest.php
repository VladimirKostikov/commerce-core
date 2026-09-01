<?php

namespace Tests\Integration;

use App\Dto\CommerceLog;
use App\Services\Messaging\KafkaCommerceEventBus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LiveMessaging;
use Tests\TestCase;

final class KafkaProduceConsumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_commerce_log_can_be_consumed(): void
    {
        config([
            'messaging.kafka.topic' => LiveMessaging::topic(),
            'messaging.kafka.group' => LiveMessaging::group(),
        ]);

        $bus = $this->app->make(KafkaCommerceEventBus::class);
        $log = new CommerceLog(
            'payment',
            'accepted',
            'ord_kafka_'.uniqid(),
            'evt_kafka',
            'paid',
            'payment accepted',
            ['amount' => 500],
        );

        $bus->publish($log);
        $got = $bus->pull(12000);

        $this->assertNotNull($got);
        $this->assertSame($log->orderId, $got->orderId);
        $this->assertSame('accepted', $got->event);
        $this->assertSame('payment', $got->channel);
    }
}
