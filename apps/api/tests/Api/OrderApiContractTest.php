<?php

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class OrderApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_json_contract(): void
    {
        $this->actingAsApi();
        $product = CommerceCatalog::productWithAvailableKey();

        $this->postJson('/api/orders', ['sku' => $product->sku])
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'sku',
                'amount',
                'currency',
                'status',
                'delivery_code',
            ]);
    }
}
