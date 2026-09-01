<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Created = 'created';
    case Paid = 'paid';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case PaymentFailed = 'payment_failed';
    case OutOfStock = 'out_of_stock';
    case DeliveryFailed = 'delivery_failed';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Delivered, self::PaymentFailed => true,
            self::Created, self::Paid, self::Delivering, self::OutOfStock, self::DeliveryFailed => false,
        };
    }

    public function isRecoverable(): bool
    {
        return match ($this) {
            self::OutOfStock, self::DeliveryFailed => true,
            self::Created, self::Paid, self::Delivering, self::Delivered, self::PaymentFailed => false,
        };
    }

    public function blocksDelivery(): bool
    {
        return $this->isFinal() || $this === self::Created;
    }

    public function canEnterDelivering(): bool
    {
        return $this === self::Paid || $this->isRecoverable();
    }

    public function isInFlight(): bool
    {
        return $this === self::Delivering;
    }
}
