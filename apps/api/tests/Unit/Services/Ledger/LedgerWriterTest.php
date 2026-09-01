<?php

namespace Tests\Unit\Services\Ledger;

use App\Enums\LedgerDirection;
use App\Enums\LedgerReason;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Services\Ledger\LedgerTotals;
use App\Services\Ledger\LedgerWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LedgerWriterTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_pair_is_balanced_and_idempotent(): void
    {
        $order = Order::factory()->paid()->create();
        $writer = $this->app->make(LedgerWriter::class);

        $writer->postPayment($order);
        $writer->postPayment($order);

        $this->assertSame(2, LedgerEntry::query()->where('order_id', $order->id)->count());
        $this->assertTrue($this->app->make(LedgerTotals::class)->balanced());
        $this->assertSame(
            $order->amount,
            (int) LedgerEntry::query()->where('reason', LedgerReason::PaymentReceived)->sum('amount'),
        );
        $this->assertSame(
            $order->amount,
            (int) LedgerEntry::query()->where('reason', LedgerReason::DeliveryLiability)->sum('amount'),
        );
    }

    public function test_issue_pair_keeps_global_balance(): void
    {
        $order = Order::factory()->paid()->create();
        $writer = $this->app->make(LedgerWriter::class);

        $writer->postPayment($order);
        $writer->postIssue($order);
        $writer->postIssue($order);

        $this->assertSame(4, LedgerEntry::query()->where('order_id', $order->id)->count());
        $this->assertSame(
            LedgerEntry::query()->where('direction', LedgerDirection::Debit)->sum('amount'),
            LedgerEntry::query()->where('direction', LedgerDirection::Credit)->sum('amount'),
        );
        $this->assertTrue($this->app->make(LedgerTotals::class)->balanced());
    }
}
