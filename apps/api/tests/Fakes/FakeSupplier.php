<?php

namespace Tests\Fakes;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;

final class FakeSupplier implements SupplierInterface
{
    public function __construct(
        private SupplierIssueResult $result,
        private SupplierName $supplierName = SupplierName::A,
    ) {
    }

    public function name(): SupplierName
    {
        return $this->supplierName;
    }

    public function issue(string $requestId, string $sku, string $orderId): SupplierIssueResult
    {
        return $this->result;
    }
}
