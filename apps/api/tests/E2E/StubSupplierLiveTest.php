<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\LiveCheckout;
use Tests\Support\LivePostgres;

final class StubSupplierLiveTest extends TestCase
{
    public function test_live_stub_returns_the_same_code_for_the_same_request_id(): void
    {
        $checkout = LiveCheckout::createOrder();
        $order = $checkout['order'];
        $requestId = 'req_live_stub_'.$order['id'];
        $payload = [
            'request_id' => $requestId,
            'sku' => $order['sku'],
            'order_id' => $order['id'],
        ];
        $url = $checkout['base'].'/api/stub/suppliers/a/issue';

        $first = HttpProbe::json('POST', $url, $payload);
        $second = HttpProbe::json('POST', $url, $payload);

        $this->assertSame(200, $first['status'], $first['body']);
        $this->assertSame(200, $second['status'], $second['body']);

        $firstBody = json_decode($first['body'], true);
        $secondBody = json_decode($second['body'], true);

        $this->assertIsArray($firstBody);
        $this->assertIsArray($secondBody);
        $this->assertSame($firstBody['code'] ?? null, $secondBody['code'] ?? null);
        $this->assertSame(1, LivePostgres::countStubIssues($requestId));
        $this->assertSame(1, LivePostgres::countKeysForOrder($order['id']));
    }
}
