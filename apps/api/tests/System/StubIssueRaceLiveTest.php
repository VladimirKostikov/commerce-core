<?php

namespace Tests\System;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\LiveCheckout;
use Tests\Support\LivePostgres;
use Tests\Support\ParallelJson;

final class StubIssueRaceLiveTest extends TestCase
{
    public function test_parallel_stub_issues_with_the_same_request_id_store_once(): void
    {
        $checkout = LiveCheckout::createOrder();
        $order = $checkout['order'];
        $requestId = 'req_live_race_'.$order['id'];
        $payload = [
            'request_id' => $requestId,
            'sku' => $order['sku'],
            'order_id' => $order['id'],
        ];
        $payloads = array_fill(0, 20, $payload);

        $codes = ParallelJson::post($checkout['base'].'/api/stub/suppliers/a/issue', $payloads);

        $this->assertCount(20, $codes);

        foreach ($codes as $code) {
            $this->assertSame('200', trim((string) $code));
        }

        $this->assertSame(1, LivePostgres::countStubIssues($requestId));
        $this->assertSame(1, LivePostgres::countKeysForOrder($order['id']));

        $show = HttpProbe::get($checkout['base'].'/api/orders/'.$order['id'], 10, [
            'Accept: application/json',
            'Authorization: Bearer '.$checkout['token'],
        ]);

        $this->assertSame(200, $show['status'], $show['body']);
    }
}
