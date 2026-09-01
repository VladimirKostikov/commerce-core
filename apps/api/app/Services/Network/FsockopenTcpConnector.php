<?php

namespace App\Services\Network;

use App\Contracts\TcpConnectorInterface;

final class FsockopenTcpConnector implements TcpConnectorInterface
{
    public function canConnect(string $host, int $port, float $timeout): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            $connection = fsockopen($host, $port, $errno, $errstr, $timeout);
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
