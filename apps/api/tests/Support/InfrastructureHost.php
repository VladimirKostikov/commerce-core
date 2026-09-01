<?php

namespace Tests\Support;

final class InfrastructureHost
{
    public static function resolve(string $service): string
    {
        $resolved = gethostbyname($service);

        return $resolved === $service ? '127.0.0.1' : $service;
    }

    public static function inDockerNetwork(): bool
    {
        return self::resolve('postgres') === 'postgres';
    }

    public static function appUrl(): string
    {
        $host = self::resolve('nginx');

        return $host === 'nginx' ? 'http://nginx' : 'http://127.0.0.1';
    }

    public static function grafanaUrl(): string
    {
        $host = self::resolve('grafana');

        return $host === 'grafana' ? 'http://grafana:3000' : 'http://127.0.0.1:3000';
    }

    public static function clickHouseUrl(): string
    {
        $host = self::resolve('clickhouse');

        return sprintf('http://%s:8123', $host);
    }

    public static function rabbitMqManagementUrl(): string
    {
        $host = self::resolve('rabbitmq');

        return sprintf('http://%s:15672', $host);
    }

    public static function kafkaPort(): int
    {
        return self::inDockerNetwork() ? 29092 : 9092;
    }
}
