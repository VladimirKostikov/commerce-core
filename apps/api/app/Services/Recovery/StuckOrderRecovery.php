<?php

namespace App\Services\Recovery;

use App\Contracts\CommerceLoggerInterface;
use App\Contracts\DeliveryServiceInterface;
use App\Contracts\StuckOrderRecoveryInterface;
use App\Services\Logging\CommerceLogFactory;

final class StuckOrderRecovery implements StuckOrderRecoveryInterface
{
    public function __construct(
        private readonly StuckOrderFinder $finder,
        private readonly DeliveryServiceInterface $delivery,
        private readonly CommerceLoggerInterface $logger,
        private readonly CommerceLogFactory $logs,
    ) {
    }

    public function recoverAll(): int
    {
        $recovered = 0;

        foreach ($this->finder->orders() as $order) {
            $updated = $this->delivery->recover($order);
            $this->logger->record($this->logs->recovery($updated));
            $recovered++;
        }

        return $recovered;
    }
}
