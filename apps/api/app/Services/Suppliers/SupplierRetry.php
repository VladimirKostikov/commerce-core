<?php

namespace App\Services\Suppliers;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;

final class SupplierRetry
{
    public function __construct(
        private readonly int $attempts,
        private readonly int $backoffMs,
    ) {
    }

    public function run(
        SupplierInterface $supplier,
        string $requestId,
        string $sku,
        string $orderId,
    ): SupplierIssueResult {
        $last = SupplierIssueResult::error('exhausted');

        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $last = $supplier->issue($requestId, $sku, $orderId);

            if ($last->isOk() || $last->isOutOfStock()) {
                return $last;
            }

            if ($attempt < $this->attempts && $this->backoffMs > 0) {
                usleep($this->backoffMs * 1000);
            }
        }

        return $last;
    }
}
