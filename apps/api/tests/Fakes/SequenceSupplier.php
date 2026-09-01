<?php

namespace Tests\Fakes;

use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;

final class SequenceSupplier implements SupplierInterface
{
    public array $requestIds = [];

    public function __construct(
        private array $results,
        private readonly SupplierName $supplierName = SupplierName::A,
    ) {
    }

    public function name(): SupplierName
    {
        return $this->supplierName;
    }

    public function issue(string $requestId, string $sku, string $orderId): SupplierIssueResult
    {
        $this->requestIds[] = $requestId;
        $next = array_shift($this->results) ?? SupplierIssueResult::error('exhausted');

        return $next->attributed($this->supplierName, $requestId);
    }
}
