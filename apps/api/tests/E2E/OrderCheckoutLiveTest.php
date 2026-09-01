<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;
use Tests\Support\PaymentWebhookPayload;

final class OrderCheckoutLiveTest extends TestCase
{
    public function test_login_create_pay_and_get_order_via_nginx(): void
    {
        $base = InfrastructureHost::appUrl();

        $login = HttpProbe::json('POST', $base.'/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        $this->assertSame(200, $login['status'], $login['body']);

        $token = json_decode($login['body'], true)['token'] ?? null;
        $this->assertIsString($token);

        $create = HttpProbe::json('POST', $base.'/api/orders', [
            'sku' => 'STEAM-TOPUP-500',
        ], ['Authorization: Bearer '.$token]);
        $this->assertSame(201, $create['status'], $create['body']);

        $order = json_decode($create['body'], true);
        $this->assertIsArray($order);
        $this->assertArrayHasKey('id', $order);

        $webhook = HttpProbe::json(
            'POST',
            $base.'/api/webhook/payment',
            PaymentWebhookPayload::paid($order['id'], 'evt_live_'.bin2hex(random_bytes(4)), $order['amount']),
        );
        $this->assertSame(200, $webhook['status'], $webhook['body']);

        $show = HttpProbe::get($base.'/api/orders/'.$order['id'], 5, [
            'Accept: application/json',
            'Authorization: Bearer '.$token,
        ]);
        $this->assertSame(200, $show['status'], $show['body']);

        $fetched = json_decode($show['body'], true);
        $this->assertIsArray($fetched);
        $this->assertContains($fetched['status'], ['delivered', 'out_of_stock', 'delivery_failed']);
    }
}
