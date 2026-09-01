<?php

namespace Tests\Unit\Services\Suppliers;

use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;
use App\Services\Suppliers\SupplierRetry;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\SequenceSupplier;

final class SupplierRetryTest extends TestCase
{
    public function test_retries_timeout_with_the_same_request_id(): void
    {
        $supplier = new SequenceSupplier([
            SupplierIssueResult::timeout(),
            SupplierIssueResult::ok('AAAA-BBBB-CCCC'),
        ]);
        $retry = new SupplierRetry(3, 0);

        $result = $retry->run($supplier, 'req_same', 'KEY-GTA5', 'ord_1');

        $this->assertTrue($result->isOk());
        $this->assertSame('AAAA-BBBB-CCCC', $result->code);
        $this->assertSame(['req_same', 'req_same'], $supplier->requestIds);
    }

    public function test_does_not_retry_out_of_stock(): void
    {
        $supplier = new SequenceSupplier([
            SupplierIssueResult::outOfStock(),
            SupplierIssueResult::ok('AAAA-BBBB-CCCC'),
        ]);

        $result = (new SupplierRetry(3, 0))->run($supplier, 'req_oos', 'KEY-GTA5', 'ord_1');

        $this->assertTrue($result->isOutOfStock());
        $this->assertCount(1, $supplier->requestIds);
    }
}
