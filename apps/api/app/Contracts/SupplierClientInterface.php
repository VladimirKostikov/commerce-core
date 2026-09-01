<?php

namespace App\Contracts;

use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;

interface SupplierClientInterface
{
    public function issue(
        SupplierName $name,
        string $requestId,
        string $sku,
        string $orderId,
    ): SupplierIssueResult;
}
