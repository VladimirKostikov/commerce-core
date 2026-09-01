<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\LiveCheckout;
use Tests\Support\LivePostgres;
use Tests\Support\ParallelJson;
use Tests\Support\PaymentWebhookPayload;

final class DuplicateEventIdRaceLiveTest extends TestCase
{
    public function test_fifty_parallel_duplicate_event_ids_are_noop(): void
    {
        $checkout = LiveCheckout::createOrder();
        $orderId = (string) $checkout['order']['id'];
        $amount = (int) $checkout['order']['amount'];
        $payload = PaymentWebhookPayload::paid($orderId, 'evt_dup_'.$orderId, $amount);
        $payloads = array_fill(0, 50, $payload);

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
        $this->assertSame(1, LivePostgres::countKeysForOrder($orderId));
        $this->assertSame(1, LivePostgres::countDeliveriesForOrder($orderId));
        $this->assertSame(1, LivePostgres::countEventsForOrder($orderId));
    }
}
