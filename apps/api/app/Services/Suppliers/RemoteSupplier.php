<?php

namespace App\Services\Suppliers;

use App\Contracts\SupplierClientInterface;
use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;

final class RemoteSupplier implements SupplierInterface
{
    public function __construct(
        private readonly SupplierName $name,
        private readonly SupplierClientInterface $client,
    ) {
    }

    public function name(): SupplierName
    {
        return $this->name;
    }

    public function issue(string $requestId, string $sku, string $orderId): SupplierIssueResult
    {
        return $this->client->issue($this->name, $requestId, $sku, $orderId);
    }
}
