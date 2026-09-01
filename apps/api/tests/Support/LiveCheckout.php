<?php

namespace Tests\Support;

use RuntimeException;

final class LiveCheckout
{
    public static function createOrder(): array
    {
        $base = InfrastructureHost::appUrl();
        $login = HttpProbe::json('POST', $base.'/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        if ($login['status'] !== 200) {
            throw new RuntimeException('Live login failed: '.$login['body']);
        }

        $token = json_decode($login['body'], true)['token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Live login returned no token');
        }

        $sku = LivePostgres::skuWithStock();
        $create = HttpProbe::json('POST', $base.'/api/orders', [
            'sku' => $sku,
        ], ['Authorization: Bearer '.$token]);

        if ($create['status'] !== 201) {
            throw new RuntimeException('Live order create failed: '.$create['body']);
        }

        $order = json_decode($create['body'], true);

        if (! is_array($order) || ! isset($order['id'], $order['amount'])) {
            throw new RuntimeException('Live order create returned no id');
        }

        return [
            'base' => $base,
            'token' => $token,
            'order' => $order,
        ];
    }
}
