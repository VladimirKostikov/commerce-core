<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\PaymentEvent;

interface PaymentWebhookServiceInterface
{
    public function handle(array $payload): void;

    public function applyPendingForOrder(Order $order): void;

    public function apply(PaymentEvent $event): void;
}
