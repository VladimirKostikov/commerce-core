<?php

namespace Tests\Unit\Services\Network;

use App\Services\Network\FsockopenTcpConnector;
use PHPUnit\Framework\TestCase;

final class FsockopenTcpConnectorTest extends TestCase
{
    public function test_closed_port_returns_false(): void
    {
        $connector = new FsockopenTcpConnector();

        $this->assertFalse($connector->canConnect('127.0.0.1', 1, 0.2));
    }
}
