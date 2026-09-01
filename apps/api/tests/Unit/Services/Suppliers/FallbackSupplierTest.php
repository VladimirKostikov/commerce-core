<?php

namespace Tests\Unit\Services\Suppliers;

use App\Dto\SupplierIssueResult;
use App\Enums\SupplierName;
use App\Services\Suppliers\FallbackSupplier;
use App\Services\Suppliers\SupplierRetry;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\SequenceSupplier;

final class FallbackSupplierTest extends TestCase
{
    public function test_falls_back_to_b_with_new_request_id(): void
    {
        $primary = new SequenceSupplier([
            SupplierIssueResult::error('unavailable'),
            SupplierIssueResult::error('unavailable'),
        ]);
        $secondary = new SequenceSupplier([
            SupplierIssueResult::ok('BBBB-CCCC-DDDD'),
        ], SupplierName::B);
        $gateway = new FallbackSupplier(new SupplierRetry(2, 0), $primary, $secondary);

        $result = $gateway->issue('req_order1', 'KEY-GTA5', 'ord_1');

        $this->assertTrue($result->isOk());
        $this->assertSame('BBBB-CCCC-DDDD', $result->code);
        $this->assertSame(SupplierName::B, $result->supplier);
        $this->assertSame(['req_order1', 'req_order1'], $primary->requestIds);
        $this->assertSame(['req_order1_b'], $secondary->requestIds);
    }

    public function test_does_not_fallback_when_primary_succeeds_after_timeout(): void
    {
        $primary = new SequenceSupplier([
            SupplierIssueResult::timeout(),
            SupplierIssueResult::ok('AAAA-BBBB-CCCC'),
        ]);
        $secondary = new SequenceSupplier([
            SupplierIssueResult::ok('SHOULD-NOT-ISSUE'),
        ], SupplierName::B);
        $gateway = new FallbackSupplier(new SupplierRetry(3, 0), $primary, $secondary);

        $result = $gateway->issue('req_order1', 'KEY-GTA5', 'ord_1');

        $this->assertSame('AAAA-BBBB-CCCC', $result->code);
        $this->assertSame(SupplierName::A, $result->supplier);
        $this->assertSame([], $secondary->requestIds);
    }
}
