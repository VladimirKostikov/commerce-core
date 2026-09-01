<?php

namespace Tests\System;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\LiveCheckout;
use Tests\Support\LivePostgres;
use Tests\Support\ParallelJson;
use Tests\Support\PaymentWebhookPayload;

final class ExactlyOnceDeliveryTest extends TestCase
{
    public function test_fifty_parallel_paid_webhooks_deliver_once(): void
    {
        $checkout = LiveCheckout::createOrder();
        $orderId = (string) $checkout['order']['id'];
        $amount = (int) $checkout['order']['amount'];
        $payloads = [];

        for ($i = 0; $i < 50; $i++) {
            $payloads[] = PaymentWebhookPayload::paid($orderId, 'evt_race_'.$orderId.'_'.$i, $amount);
        }

        $codes = ParallelJson::post($checkout['base'].'/api/webhook/payment', $payloads);

        $this->assertCount(50, $codes);

        foreach ($codes as $code) {
            $this->assertSame('200', trim((string) $code));
        }

        $show = HttpProbe::get($checkout['base'].'/api/orders/'.$orderId, 10, [
            'Accept: application/json',
            'Authorization: Bearer '.$checkout['token'],
        ]);
        $fetched = json_decode($show['body'], true);

        $this->assertSame(200, $show['status'], $show['body']);
        $this->assertIsArray($fetched);
        $this->assertSame('delivered', $fetched['status']);
        $this->assertNotSame('', $fetched['delivery_code'] ?? '');
        $this->assertSame(1, LivePostgres::countKeysForOrder($orderId));
        $this->assertSame(1, LivePostgres::countDeliveriesForOrder($orderId));
        $this->assertSame(50, LivePostgres::countEventsForOrder($orderId));
    }
}
