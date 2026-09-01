<?php

namespace App\Services\Reconciliation;

use App\Contracts\ReconciliationServiceInterface;
use App\Dto\ReconciliationReport;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Ledger\LedgerTotals;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ReconciliationService implements ReconciliationServiceInterface
{
    public function __construct(
        private readonly LedgerTotals $ledger,
    ) {
    }

    public function report(): ReconciliationReport
    {
        return new ReconciliationReport(
            $this->summarize($this->paidNotDelivered()),
            $this->summarize($this->deliveredNotPaid()),
            $this->ledger->debit(),
            $this->ledger->credit(),
        );
    }

    private function paidNotDelivered(): Collection
    {
        return Order::query()
            ->where('status', '!=', OrderStatus::Delivered)
            ->where(function (Builder $query): void {
                $query->whereNull('delivery_code')->orWhere('delivery_code', '');
            })
            ->whereHas('paymentEvents', function (Builder $query): void {
                $query->where('status', PaymentStatus::Paid)
                    ->whereNotNull('processed_at');
            })
            ->orderBy('id')
            ->get();
    }

    private function deliveredNotPaid(): Collection
    {
        return Order::query()
            ->where(function (Builder $query): void {
                $query->where('status', OrderStatus::Delivered)
                    ->orWhereNotNull('delivery_code');
            })
            ->whereDoesntHave('paymentEvents', function (Builder $query): void {
                $query->where('status', PaymentStatus::Paid)
                    ->whereNotNull('processed_at');
            })
            ->orderBy('id')
            ->get();
    }

    private function summarize(Collection $orders): array
    {
        return $orders->map(fn (Order $order): array => [
            'id' => $order->id,
            'status' => $order->status->value,
            'amount' => $order->amount,
            'delivery_code' => $order->delivery_code,
        ])->all();
    }
}
