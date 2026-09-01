<?php

namespace App\Services\Suppliers;

use App\Contracts\SupplierClientInterface;
use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class HttpSupplierClient implements SupplierClientInterface
{
    public function issue(
        SupplierName $name,
        string $requestId,
        string $sku,
        string $orderId,
    ): SupplierIssueResult {
        $url = (string) config('suppliers.'.$name->value.'.url');
        $timeout = (float) config('suppliers.timeout_seconds');

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($url, [
                    'request_id' => $requestId,
                    'sku' => $sku,
                    'order_id' => $orderId,
                ]);
        } catch (ConnectionException) {
            return SupplierIssueResult::timeout()->attributed($name, $requestId);
        }

        if ($response->serverError()) {
            return SupplierIssueResult::error('unavailable')->attributed($name, $requestId);
        }

        $status = (string) $response->json('status', '');
        $code = $response->json('code');
        $reason = (string) $response->json('reason', 'error');

        if ($status === 'ok' && is_string($code) && $code !== '') {
            return SupplierIssueResult::ok($code)->attributed($name, $requestId);
        }

        if ($reason === 'out_of_stock' || $status === 'out_of_stock') {
            return SupplierIssueResult::outOfStock()->attributed($name, $requestId);
        }

        return SupplierIssueResult::error($reason)->attributed($name, $requestId);
    }
}
