<?php

namespace Tests\Performance;

use App\Dto\CommerceLog;
use App\Dto\CommerceWork;
use App\Services\Catalog\StorefrontCache;
use App\Services\Messaging\KafkaCommerceEventBus;
use App\Services\Messaging\RabbitMqCommerceWorkQueue;
use Illuminate\Support\Facades\Cache;
use Tests\Support\LiveMessaging;
use Tests\TestCase;

final class MessagingVolumeTest extends TestCase
{
    public function test_redis_storefront_cache_handles_volume_reads(): void
    {
        config([
            'cache.default' => 'redis',
            'cache.prefix' => 'phpunit_vol_'.uniqid(),
        ]);
        Cache::flush();
        $cache = new StorefrontCache();
        $cache->remember(10, fn (): array => [['sku' => 'VOL']]);

        $startedAt = microtime(true);
        for ($i = 0; $i < 80; $i++) {
            $this->assertSame([['sku' => 'VOL']], $cache->remember(10, fn (): array => [['sku' => 'MISS']]));
        }

        $this->assertLessThan(2.0, microtime(true) - $startedAt);
        Cache::flush();
    }

    public function test_kafka_accepts_a_volume_of_events(): void
    {
        config([
            'messaging.kafka.topic' => LiveMessaging::topic(),
            'messaging.kafka.group' => LiveMessaging::group(),
        ]);
        $bus = $this->app->make(KafkaCommerceEventBus::class);
        $batch = 'ord_vol_'.uniqid();

        for ($i = 0; $i < 5; $i++) {
            $bus->publish(new CommerceLog('delivery', 'issued', $batch, 'ref_'.$i, 'delivered', 'ok'));
        }

        $seen = 0;
        for ($i = 0; $i < 5; $i++) {
            $got = $bus->pull(8000);
            if ($got !== null && $got->orderId === $batch) {
                $seen++;
            }
        }

        $this->assertSame(5, $seen);
    }

    public function test_rabbitmq_spike_of_notices_drains(): void
    {
        config([
            'messaging.rabbitmq.queue' => LiveMessaging::queue(),
        ]);
        $queue = $this->app->make(RabbitMqCommerceWorkQueue::class);
        $batch = 'ord_spike_'.uniqid();

        for ($i = 0; $i < 30; $i++) {
            $queue->push(new CommerceWork($batch, 'issued'));
        }

        $seen = 0;
        for ($i = 0; $i < 30; $i++) {
            if ($queue->pull()?->orderId === $batch) {
                $seen++;
            }
        }

        $this->assertSame(30, $seen);
    }

    public function test_rabbitmq_accepts_a_volume_of_notices(): void
    {
        config([
            'messaging.rabbitmq.queue' => LiveMessaging::queue(),
        ]);
        $queue = $this->app->make(RabbitMqCommerceWorkQueue::class);
        $batch = 'ord_vol_'.uniqid();

        for ($i = 0; $i < 20; $i++) {
            $queue->push(new CommerceWork($batch, 'issued'));
        }

        $seen = 0;
        for ($i = 0; $i < 20; $i++) {
            $got = $queue->pull();
            if ($got !== null && $got->orderId === $batch) {
                $seen++;
            }
        }

        $this->assertSame(20, $seen);
    }
}
