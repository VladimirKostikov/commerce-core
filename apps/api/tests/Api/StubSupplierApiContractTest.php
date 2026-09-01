<?php

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StubSupplierApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_validation_contract(): void
    {
        $this->postJson('/api/stub/suppliers/a/issue', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id', 'sku', 'order_id']);
    }

    public function test_unknown_supplier_is_not_found(): void
    {
        $this->postJson('/api/stub/suppliers/z/issue', [
            'request_id' => 'req_x',
            'sku' => 'KEY-GTA5',
            'order_id' => 'ord_x',
        ])->assertNotFound()
            ->assertJsonPath('status', 'error');
    }
}
