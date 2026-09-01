<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redis;
use Tests\Support\InfrastructureHost;

final class RedisCommandsTest extends TestCase
{
    public function test_redis_ping_and_set_get(): void
    {
        $redis = new Redis();
        $connected = $redis->connect(InfrastructureHost::resolve('redis'), 6379, 2.0);

        $this->assertTrue($connected);

        $pong = $redis->ping();
        $this->assertTrue($pong === true || $pong === '+PONG' || $pong === 'PONG');

        $key = 'stack:test:'.uniqid('', true);
        $this->assertTrue($redis->set($key, 'ok', ['EX' => 30]));
        $this->assertSame('ok', $redis->get($key));
        $this->assertSame(1, $redis->del($key));
    }
}
