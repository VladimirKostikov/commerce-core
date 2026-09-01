<?php

namespace App\Contracts;

use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;

interface SupplierInterface
{
    public function name(): SupplierName;

    public function issue(string $requestId, string $sku, string $orderId): SupplierIssueResult;
}
