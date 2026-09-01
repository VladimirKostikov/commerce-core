<?php

namespace App\Services\Messaging;

use App\Dto\CommerceWork;
use JsonException;

final class CommerceWorkCodec
{
    public function encode(CommerceWork $work): string
    {
        return json_encode([
            'order_id' => $work->orderId,
            'kind' => $work->kind,
        ], JSON_THROW_ON_ERROR);
    }

    public function decode(string $payload): CommerceWork
    {
        $row = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($row) || ! isset($row['order_id'], $row['kind'])) {
            throw new JsonException('Commerce work payload is invalid');
        }

        return new CommerceWork((string) $row['order_id'], (string) $row['kind']);
    }
}
