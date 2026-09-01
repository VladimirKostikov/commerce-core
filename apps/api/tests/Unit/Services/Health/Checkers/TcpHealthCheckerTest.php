<?php

namespace Tests\Unit\Services\Health\Checkers;

use App\Contracts\TcpConnectorInterface;
use App\Services\Health\Checkers\KafkaHealthChecker;
use App\Services\Health\Checkers\RabbitMqHealthChecker;
use Tests\TestCase;

final class TcpHealthCheckerTest extends TestCase
{
    public function test_kafka_checker_passes_when_tcp_connects(): void
    {
        config()->set('health.kafka.host', 'kafka');
        config()->set('health.kafka.port', 29092);
        config()->set('health.timeout', 1);

        $connector = $this->createMock(TcpConnectorInterface::class);
        $connector->expects($this->once())
            ->method('canConnect')
            ->with('kafka', 29092, 1.0)
            ->willReturn(true);

        $checker = new KafkaHealthChecker($connector);
        $result = $checker->check();

        $this->assertTrue($result->ok);
        $this->assertSame('kafka', $result->name);
    }

    public function test_rabbitmq_checker_fails_when_tcp_is_down(): void
    {
        config()->set('health.rabbitmq.host', 'rabbitmq');
        config()->set('health.rabbitmq.port', 5672);
        config()->set('health.timeout', 1);

        $connector = $this->createMock(TcpConnectorInterface::class);
        $connector->method('canConnect')->willReturn(false);

        $checker = new RabbitMqHealthChecker($connector);
        $result = $checker->check();

        $this->assertFalse($result->ok);
        $this->assertSame('rabbitmq', $result->name);
        $this->assertSame('Connection refused', $result->message);
    }
}
