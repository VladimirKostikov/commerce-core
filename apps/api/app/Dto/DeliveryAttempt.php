<?php

namespace App\Dto;

use App\Models\Order;

final readonly class DeliveryAttempt
{
    public function __construct(
        public Order $order,
        public string $requestId,
        public string $sku,
        public bool $shouldIssue,
    ) {
    }
}
