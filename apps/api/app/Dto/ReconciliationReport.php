<?php

namespace App\Dto;

final readonly class ReconciliationReport
{
    public function __construct(
        public array $paidNotDelivered,
        public array $deliveredNotPaid,
        public int $debitTotal,
        public int $creditTotal,
    ) {
    }

    public function ledgerBalanced(): bool
    {
        return $this->debitTotal === $this->creditTotal;
    }

    public function toArray(): array
    {
        return [
            'paid_not_delivered' => $this->paidNotDelivered,
            'delivered_not_paid' => $this->deliveredNotPaid,
            'ledger' => [
                'debit' => $this->debitTotal,
                'credit' => $this->creditTotal,
                'balanced' => $this->ledgerBalanced(),
            ],
        ];
    }
}
