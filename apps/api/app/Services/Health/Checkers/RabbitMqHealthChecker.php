<?php

namespace App\Services\Health\Checkers;

use App\Contracts\HealthCheckerInterface;
use App\Contracts\TcpConnectorInterface;
use App\Services\Health\HealthCheckResult;

final class RabbitMqHealthChecker implements HealthCheckerInterface
{
    public function __construct(
        private readonly TcpConnectorInterface $tcpConnector,
    ) {
    }

    public function name(): string
    {
        return 'rabbitmq';
    }

    public function check(): HealthCheckResult
    {
        $host = (string) config('health.rabbitmq.host');
        $port = (int) config('health.rabbitmq.port');
        $timeout = (float) config('health.timeout');

        if ($this->tcpConnector->canConnect($host, $port, $timeout)) {
            return HealthCheckResult::ok($this->name());
        }

        return HealthCheckResult::fail($this->name(), 'Connection refused');
    }
}
