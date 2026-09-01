<?php

namespace App\Services\Ledger;

use App\Contracts\LedgerWriterInterface;
use App\Enums\Currency;
use App\Enums\LedgerDirection;
use App\Enums\LedgerReason;
use App\Models\LedgerEntry;
use App\Models\Order;
use Illuminate\Database\UniqueConstraintViolationException;

final class LedgerWriter implements LedgerWriterInterface
{
    public function postPayment(Order $order): void
    {
        $this->postPair(
            $order,
            LedgerReason::PaymentReceived,
            LedgerReason::DeliveryLiability,
        );
    }

    public function postIssue(Order $order): void
    {
        $this->postPair(
            $order,
            LedgerReason::LiabilityCleared,
            LedgerReason::KeysIssued,
        );
    }

    private function postPair(Order $order, LedgerReason $debit, LedgerReason $credit): void
    {
        if ($this->exists($order, $debit)) {
            return;
        }

        $now = now();
        $currency = $order->currency instanceof Currency
            ? $order->currency->value
            : (string) $order->currency;

        try {
            LedgerEntry::query()->insert([
                $this->row($order, LedgerDirection::Debit, $debit, $currency, $now),
                $this->row($order, LedgerDirection::Credit, $credit, $currency, $now),
            ]);
        } catch (UniqueConstraintViolationException) {
        }
    }

    private function exists(Order $order, LedgerReason $reason): bool
    {
        return LedgerEntry::query()
            ->where('order_id', $order->id)
            ->where('reason', $reason)
            ->exists();
    }

    private function row(
        Order $order,
        LedgerDirection $direction,
        LedgerReason $reason,
        string $currency,
        mixed $now,
    ): array {
        return [
            'order_id' => $order->id,
            'direction' => $direction->value,
            'amount' => $order->amount,
            'currency' => $currency,
            'reason' => $reason->value,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
