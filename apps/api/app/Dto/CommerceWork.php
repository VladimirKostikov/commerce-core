<?php

namespace App\Dto;

final readonly class CommerceWork
{
    public function __construct(
        public string $orderId,
        public string $kind,
    ) {
    }
}
