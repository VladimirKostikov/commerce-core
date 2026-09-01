<?php

namespace App\Services\Suppliers;

use App\Contracts\ProductKeyInventoryInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;
use App\Models\StubSupplierIssue;
use App\Services\Catalog\ProductFinder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

final class StubIssueService
{
    public function __construct(
        private readonly ProductKeyInventoryInterface $inventory,
        private readonly ProductFinder $products,
    ) {
    }

    public function find(SupplierName $supplier, string $requestId): ?string
    {
        $row = StubSupplierIssue::query()
            ->where('supplier', $supplier)
            ->where('request_id', $requestId)
            ->first();

        return $row?->code;
    }

    public function issue(
        SupplierName $supplier,
        string $requestId,
        string $sku,
        string $orderId,
    ): SupplierIssueResult {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $existing = $this->find($supplier, $requestId);

            if ($existing !== null) {
                return SupplierIssueResult::ok($existing)->attributed($supplier, $requestId);
            }

            try {
                return $this->issueNew($supplier, $requestId, $sku, $orderId);
            } catch (UniqueConstraintViolationException|QueryException) {
            }
        }

        $existing = $this->find($supplier, $requestId);

        if ($existing !== null) {
            return SupplierIssueResult::ok($existing)->attributed($supplier, $requestId);
        }

        return SupplierIssueResult::error('unavailable')->attributed($supplier, $requestId);
    }

    private function issueNew(
        SupplierName $supplier,
        string $requestId,
        string $sku,
        string $orderId,
    ): SupplierIssueResult {
        $product = $this->products->bySku($sku);

        if ($product === null) {
            return SupplierIssueResult::error('unknown_sku')->attributed($supplier, $requestId);
        }

        $key = $this->inventory->claim($product->id, $orderId);

        if ($key === null) {
            return SupplierIssueResult::outOfStock()->attributed($supplier, $requestId);
        }

        $this->remember($supplier, $requestId, $sku, $orderId, $key->code);

        return SupplierIssueResult::ok($key->code)->attributed($supplier, $requestId);
    }

    private function remember(
        SupplierName $supplier,
        string $requestId,
        string $sku,
        string $orderId,
        string $code,
    ): void {
        try {
            StubSupplierIssue::query()->create([
                'supplier' => $supplier,
                'request_id' => $requestId,
                'sku' => $sku,
                'order_id' => $orderId,
                'code' => $code,
            ]);
        } catch (UniqueConstraintViolationException|QueryException) {
        }
    }
}
