<?php

namespace Tests\Performance;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Services\Ledger\LedgerTotals;
use App\Services\Ledger\LedgerWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LedgerVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_of_ledger_pairs_stays_balanced(): void
    {
        $writer = $this->app->make(LedgerWriter::class);

        for ($i = 0; $i < 25; $i++) {
            $order = Order::factory()->paid()->create();
            $writer->postPayment($order);
            $writer->postIssue($order);
        }

        $this->assertTrue($this->app->make(LedgerTotals::class)->balanced());
        $this->assertSame(100, LedgerEntry::query()->count());
    }
}
