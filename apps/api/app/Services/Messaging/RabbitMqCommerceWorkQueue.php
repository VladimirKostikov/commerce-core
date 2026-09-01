<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceWorkQueueInterface;
use App\Dto\CommerceWork;
use Throwable;

final class RabbitMqCommerceWorkQueue implements CommerceWorkQueueInterface
{
    public function __construct(
        private readonly CommerceWorkCodec $codec,
        private readonly RabbitMqBroker $broker,
    ) {
    }

    public function push(CommerceWork $work): void
    {
        try {
            $this->broker->publish($this->codec->encode($work));
        } catch (Throwable) {
        }
    }

    public function pull(): ?CommerceWork
    {
        try {
            $payload = $this->broker->get();
        } catch (Throwable) {
            return null;
        }

        if ($payload === null) {
            return null;
        }

        try {
            return $this->codec->decode($payload);
        } catch (Throwable) {
            return null;
        }
    }
}
