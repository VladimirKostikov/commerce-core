<?php

namespace Tests\Integration;

use App\Dto\CommerceLog;
use App\Jobs\RecoverStuckOrdersJob;
use App\Services\Catalog\StorefrontCache;
use App\Services\Messaging\RedisCommerceInbox;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

final class RedisMessagingTest extends TestCase
{
    public function test_inbox_writes_notices_and_last_event(): void
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $noticesKey = 'commerce:notices:'.$suffix;
        $eventsKey = 'commerce:events:inbox:'.$suffix;
        $lastKey = 'commerce:events:last:'.$suffix;

        config([
            'messaging.redis.notices_key' => $noticesKey,
            'messaging.redis.events_key' => $eventsKey,
            'messaging.redis.last_event_key' => $lastKey,
        ]);

        $inbox = new RedisCommerceInbox();
        $inbox->rememberNotice('ord_n', 'issued');
        $inbox->rememberEvent(new CommerceLog('payment', 'accepted', 'ord_e', 'evt', 'paid', 'ok'));

        $this->assertSame('ord_e:accepted', $inbox->lastEvent());
        $this->assertSame('issued', $inbox->notices()[0]['kind']);
        $this->assertSame('ord_e', $inbox->events()[0]['order_id']);
        $this->assertSame('ord_e:accepted', Redis::get($lastKey));
        $this->assertStringContainsString('issued', (string) Redis::lindex($noticesKey, 0));
        $this->assertStringContainsString('ord_e', (string) Redis::lindex($eventsKey, 0));
        Redis::del($noticesKey, $eventsKey, $lastKey);
    }

    public function test_storefront_cache_uses_redis_store(): void
    {
        config([
            'cache.default' => 'redis',
            'cache.prefix' => 'phpunit_'.uniqid(),
        ]);
        Cache::flush();

        $cache = new StorefrontCache();
        $cache->remember(3, fn (): array => [['sku' => 'REDIS-HOT']]);

        $this->assertSame([['sku' => 'REDIS-HOT']], $cache->remember(3, fn (): array => [['sku' => 'MISS']]));
        $cache->bust();
        $this->assertSame([['sku' => 'AFTER']], $cache->remember(3, fn (): array => [['sku' => 'AFTER']]));
        Cache::flush();
    }

    public function test_recovery_job_lands_on_redis_queue(): void
    {
        config(['queue.default' => 'redis']);
        $name = 'phpunit-'.str_replace('.', '', uniqid('', true));

        RecoverStuckOrdersJob::dispatch()->onQueue($name);

        $this->assertSame(1, Queue::size($name));
        Redis::del('queues:'.$name);
        $this->assertSame(0, Queue::size($name));
    }
}
