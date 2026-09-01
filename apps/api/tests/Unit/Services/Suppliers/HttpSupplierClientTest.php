<?php

namespace Tests\Unit\Services\Suppliers;

use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;
use App\Services\Suppliers\HttpSupplierClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class HttpSupplierClientTest extends TestCase
{
    public function test_ok_response_is_mapped(): void
    {
        Http::fake([
            '*' => Http::response([
                'status' => 'ok',
                'request_id' => 'req_http',
                'code' => 'AAAA-BBBB-CCCC',
            ], 200),
        ]);

        $result = (new HttpSupplierClient())->issue(SupplierName::A, 'req_http', 'KEY-GTA5', 'ord_1');

        $this->assertTrue($result->isOk());
        $this->assertSame('AAAA-BBBB-CCCC', $result->code);
        $this->assertSame(SupplierName::A, $result->supplier);
    }

    public function test_timeout_is_not_a_hard_error(): void
    {
        Http::fake(function () {
            throw new ConnectionException('timed out');
        });

        $result = (new HttpSupplierClient())->issue(SupplierName::A, 'req_http', 'KEY-GTA5', 'ord_1');

        $this->assertTrue($result->isTimeout());
        $this->assertFalse($result->isOk());
    }

    public function test_unavailable_is_mapped_from_server_error(): void
    {
        Http::fake(['*' => Http::response(['status' => 'error', 'reason' => 'unavailable'], 503)]);

        $result = (new HttpSupplierClient())->issue(SupplierName::A, 'req_http', 'KEY-GTA5', 'ord_1');

        $this->assertSame(SupplierIssueResult::STATUS_ERROR, $result->status);
        $this->assertSame('unavailable', $result->reason);
    }
}
