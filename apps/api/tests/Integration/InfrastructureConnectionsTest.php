<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\InfrastructureHost;

final class InfrastructureConnectionsTest extends TestCase
{
    public function test_postgres_accepts_tcp_connections(): void
    {
        $this->assertTrue(
            $this->canConnect(InfrastructureHost::resolve('postgres'), 5432),
            'PostgreSQL is not reachable on port 5432',
        );
    }

    public function test_redis_accepts_tcp_connections(): void
    {
        $this->assertTrue(
            $this->canConnect(InfrastructureHost::resolve('redis'), 6379),
            'Redis is not reachable on port 6379',
        );
    }

    public function test_rabbitmq_accepts_tcp_connections(): void
    {
        $this->assertTrue(
            $this->canConnect(InfrastructureHost::resolve('rabbitmq'), 5672),
            'RabbitMQ is not reachable on port 5672',
        );
    }

    public function test_kafka_accepts_tcp_connections(): void
    {
        $this->assertTrue(
            $this->canConnect(InfrastructureHost::resolve('kafka'), InfrastructureHost::kafkaPort()),
            'Kafka is not reachable',
        );
    }

    public function test_clickhouse_accepts_http_and_native_ports(): void
    {
        $host = InfrastructureHost::resolve('clickhouse');

        $this->assertTrue($this->canConnect($host, 8123), 'ClickHouse HTTP port 8123 is down');
        $this->assertTrue($this->canConnect($host, 9000), 'ClickHouse native port 9000 is down');
    }

    public function test_grafana_accepts_tcp_connections(): void
    {
        $this->assertTrue(
            $this->canConnect(InfrastructureHost::resolve('grafana'), 3000),
            'Grafana is not reachable on port 3000',
        );
    }

    private function canConnect(string $host, int $port): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            $connection = fsockopen($host, $port, $errno, $errstr, 2);
        } finally {
            restore_error_handler();
        }

        if (! is_resource($connection)) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
