<?php

namespace App\Dto;

final readonly class CommerceLog
{
    public function __construct(
        public string $channel,
        public string $event,
        public string $orderId,
        public string $reference,
        public string $status,
        public string $message,
        public array $context = [],
    ) {
    }

    public function toRow(string $eventTime): array
    {
        return [
            'event_time' => $eventTime,
            'channel' => $this->channel,
            'event' => $this->event,
            'order_id' => $this->orderId,
            'reference' => $this->reference,
            'status' => $this->status,
            'message' => $this->message,
            'context' => json_encode($this->context, JSON_THROW_ON_ERROR),
        ];
    }
}
