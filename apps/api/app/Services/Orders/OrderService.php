<?php

namespace App\Services\Orders;

use App\Contracts\OrderServiceInterface;
use App\Contracts\PaymentWebhookServiceInterface;
use App\Enums\OrderStatus;
use App\Exceptions\OrderNotFoundException;
use App\Models\Order;
use App\Models\User;
use App\Services\Catalog\ProductFinder;

final class OrderService implements OrderServiceInterface
{
    public function __construct(
        private readonly ProductFinder $products,
        private readonly PaymentWebhookServiceInterface $paymentWebhookService,
    ) {
    }

    public function create(User $user, string $sku): Order
    {
        $product = $this->products->activeBySku($sku);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
            'currency' => $product->currency,
            'status' => OrderStatus::Created,
        ]);

        $this->paymentWebhookService->applyPendingForOrder($order);

        return $order->fresh() ?? $order;
    }

    public function findForUser(User $user, string $id): Order
    {
        $order = Order::query()
            ->where('user_id', $user->id)
            ->find($id);

        if ($order === null) {
            throw new OrderNotFoundException();
        }

        return $order;
    }
}
