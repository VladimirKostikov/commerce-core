<?php

namespace App\Services\Suppliers;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;

final class FallbackSupplier implements SupplierInterface
{
    public function __construct(
        private readonly SupplierRetry $retry,
        private readonly SupplierInterface $primary,
        private readonly SupplierInterface $secondary,
    ) {
    }

    public function name(): SupplierName
    {
        return $this->primary->name();
    }

    public function issue(string $requestId, string $sku, string $orderId): SupplierIssueResult
    {
        $primary = $this->retry->run($this->primary, $requestId, $sku, $orderId);

        if ($primary->isOk() || $primary->isOutOfStock()) {
            return $primary;
        }

        return $this->retry->run($this->secondary, $requestId.'_b', $sku, $orderId);
    }
}
