<?php

namespace App\Services\Suppliers;

use App\Contracts\SupplierClientInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierMode;
use App\Enums\SupplierName;

final class DirectSupplierClient implements SupplierClientInterface
{
    public function __construct(
        private readonly StubIssueService $issuer,
    ) {
    }

    public function issue(
        SupplierName $name,
        string $requestId,
        string $sku,
        string $orderId,
    ): SupplierIssueResult {
        $mode = SupplierMode::tryFrom((string) config('suppliers.'.$name->value.'.mode')) ?? SupplierMode::Ok;

        if ($mode === SupplierMode::Fail) {
            return SupplierIssueResult::error('unavailable')->attributed($name, $requestId);
        }

        $known = $this->issuer->find($name, $requestId);

        if ($known !== null) {
            return SupplierIssueResult::ok($known)->attributed($name, $requestId);
        }

        $result = $this->issuer->issue($name, $requestId, $sku, $orderId);

        if ($mode === SupplierMode::Hang && $result->isOk()) {
            return SupplierIssueResult::timeout()->attributed($name, $requestId);
        }

        return $result;
    }
}
