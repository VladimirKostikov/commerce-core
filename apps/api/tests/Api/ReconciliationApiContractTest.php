<?php

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReconciliationApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_json_contract(): void
    {
        $this->getJson('/api/reconciliation')
            ->assertOk()
            ->assertJsonStructure([
                'paid_not_delivered',
                'delivered_not_paid',
                'ledger' => ['debit', 'credit', 'balanced'],
            ])
            ->assertJsonPath('ledger.balanced', true);
    }
}
