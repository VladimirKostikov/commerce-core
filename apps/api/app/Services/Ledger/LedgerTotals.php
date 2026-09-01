<?php

namespace App\Services\Ledger;

use App\Enums\LedgerDirection;
use App\Models\LedgerEntry;

final class LedgerTotals
{
    public function debit(): int
    {
        return (int) LedgerEntry::query()
            ->where('direction', LedgerDirection::Debit)
            ->sum('amount');
    }

    public function credit(): int
    {
        return (int) LedgerEntry::query()
            ->where('direction', LedgerDirection::Credit)
            ->sum('amount');
    }

    public function balanced(): bool
    {
        return $this->debit() === $this->credit();
    }
}
