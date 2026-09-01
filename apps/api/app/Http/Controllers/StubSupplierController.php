<?php

namespace App\Http\Controllers;

use App\Enums\SupplierMode;
use App\Enums\SupplierName;
use App\Http\Requests\StubIssueRequest;
use App\Services\Suppliers\StubIssueService;
use Illuminate\Http\JsonResponse;

final class StubSupplierController extends Controller
{
    public function __invoke(
        StubIssueRequest $request,
        string $supplier,
        StubIssueService $issuer,
    ): JsonResponse {
        $name = SupplierName::tryFrom($supplier);

        if ($name === null) {
            return response()->json(['status' => 'error', 'reason' => 'unknown_supplier'], 404);
        }

        $mode = SupplierMode::tryFrom((string) config('suppliers.'.$name->value.'.mode')) ?? SupplierMode::Ok;

        if ($mode === SupplierMode::Fail) {
            return response()->json(['status' => 'error', 'reason' => 'unavailable'], 503);
        }

        $result = $issuer->issue(
            $name,
            $request->string('request_id')->toString(),
            $request->string('sku')->toString(),
            $request->string('order_id')->toString(),
        );

        if ($result->isOk()) {
            return response()->json([
                'status' => 'ok',
                'request_id' => $request->string('request_id')->toString(),
                'code' => $result->code,
            ]);
        }

        if ($result->isOutOfStock()) {
            return response()->json(['status' => 'error', 'reason' => 'out_of_stock']);
        }

        return response()->json([
            'status' => 'error',
            'reason' => $result->reason ?? 'error',
        ], 422);
    }
}
