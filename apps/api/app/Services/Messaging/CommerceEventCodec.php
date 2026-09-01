<?php

namespace App\Services\Messaging;

use App\Dto\CommerceLog;
use JsonException;

final class CommerceEventCodec
{
    public function encode(CommerceLog $log): string
    {
        return json_encode($log->toRow(gmdate('Y-m-d H:i:s')), JSON_THROW_ON_ERROR);
    }

    public function decode(string $payload): CommerceLog
    {
        $row = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($row)) {
            throw new JsonException('Commerce event is not an object');
        }

        return new CommerceLog(
            (string) ($row['channel'] ?? ''),
            (string) ($row['event'] ?? ''),
            (string) ($row['order_id'] ?? ''),
            (string) ($row['reference'] ?? ''),
            (string) ($row['status'] ?? ''),
            (string) ($row['message'] ?? ''),
            $this->context($row['context'] ?? []),
        );
    }

    private function context(mixed $context): array
    {
        if (is_array($context)) {
            return $context;
        }

        if (! is_string($context) || $context === '') {
            return [];
        }

        $decoded = json_decode($context, true);

        return is_array($decoded) ? $decoded : [];
    }
}
