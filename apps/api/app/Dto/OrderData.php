<?php

namespace App\Dto;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Models\Order;

final readonly class OrderData
{
    public function __construct(
        public string $id,
        public string $sku,
        public int $amount,
        public string $currency,
        public string $status,
        public ?string $deliveryCode,
    ) {
    }

    public static function fromModel(Order $order): self
    {
        $currency = $order->currency instanceof Currency
            ? $order->currency->value
            : (string) $order->currency;
        $status = $order->status instanceof OrderStatus
            ? $order->status->value
            : (string) $order->status;

        return new self(
            $order->id,
            $order->sku,
            $order->amount,
            $currency,
            $status,
            $order->delivery_code,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'delivery_code' => $this->deliveryCode,
        ];
    }
};
