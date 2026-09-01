<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceEventBusInterface;
use App\Contracts\CommerceInboxInterface;
use App\Contracts\CommerceMessageProcessorInterface;
use App\Contracts\CommerceWorkQueueInterface;

final class CommerceMessageProcessor implements CommerceMessageProcessorInterface
{
    public function __construct(
        private readonly CommerceEventBusInterface $events,
        private readonly CommerceWorkQueueInterface $work,
        private readonly CommerceInboxInterface $inbox,
    ) {
    }

    public function consumeEvents(int $max, int $timeoutMs): int
    {
        $handled = 0;

        while ($handled < $max) {
            $log = $this->events->pull($timeoutMs);

            if ($log === null) {
                break;
            }

            $this->inbox->rememberEvent($log);
            $handled++;
        }

        return $handled;
    }

    public function consumeNotices(int $max): int
    {
        $handled = 0;

        while ($handled < $max) {
            $work = $this->work->pull();

            if ($work === null) {
                break;
            }

            $this->inbox->rememberNotice($work->orderId, $work->kind);
            $handled++;
        }

        return $handled;
    }
}
