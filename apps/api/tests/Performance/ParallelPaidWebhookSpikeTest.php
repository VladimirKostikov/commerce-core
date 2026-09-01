<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\LiveCheckout;
use Tests\Support\LivePostgres;
use Tests\Support\ParallelJson;
use Tests\Support\PaymentWebhookPayload;

final class ParallelPaidWebhookSpikeTest extends TestCase
{
    public function test_parallel_paid_spike_stays_exactly_once(): void
    {
        $checkout = LiveCheckout::createOrder();
        $orderId = (string) $checkout['order']['id'];
        $amount = (int) $checkout['order']['amount'];
        $payloads = [];

        for ($i = 0; $i < 50; $i++) {
            $payloads[] = PaymentWebhookPayload::paid($orderId, 'evt_spike_'.$orderId.'_'.$i, $amount);
        }

        $codes = ParallelJson::post($checkout['base'].'/api/webhook/payment', $payloads, 45);

        $this->assertCount(50, $codes);

        foreach ($codes as $code) {
            $this->assertSame('200', trim((string) $code));
        }

        $this->assertSame(1, LivePostgres::countKeysForOrder($orderId));
        $this->assertSame(1, LivePostgres::countDeliveriesForOrder($orderId));
    }
}
