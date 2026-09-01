<?php

namespace App\Contracts;

interface TcpConnectorInterface
{
    public function canConnect(string $host, int $port, float $timeout): bool;
}
