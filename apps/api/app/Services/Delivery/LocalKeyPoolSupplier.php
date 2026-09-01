<?php

namespace App\Services\Delivery;

use App\Contracts\ProductKeyInventoryInterface;
use App\Contracts\SupplierInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;
use App\Models\Delivery;
use App\Services\Catalog\ProductFinder;

final class LocalKeyPoolSupplier implements SupplierInterface
{
    public function __construct(
        private readonly ProductKeyInventoryInterface $inventory,
        private readonly ProductFinder $products,
    ) {
    }

    public function name(): SupplierName
    {
        return SupplierName::A;
    }

    public function issue(string $requestId, string $sku, string $orderId): SupplierIssueResult
    {
        $issued = $this->alreadyIssued($requestId);

        if ($issued !== null) {
            return SupplierIssueResult::ok($issued);
        }

        $product = $this->products->bySku($sku);

        if ($product === null) {
            return SupplierIssueResult::error('unknown_sku');
        }

        $key = $this->inventory->claim($product->id, $orderId);

        if ($key === null) {
            return SupplierIssueResult::outOfStock();
        }

        return SupplierIssueResult::ok($key->code);
    }

    private function alreadyIssued(string $requestId): ?string
    {
        $delivery = Delivery::query()
            ->where('request_id', $requestId)
            ->whereNotNull('code')
            ->first();

        return $delivery?->code;
    }
}
