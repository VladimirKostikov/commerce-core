<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class OrderCreateVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_of_order_creates_succeed(): void
    {
        $this->actingAsApi();
        $product = CommerceCatalog::productWithAvailableKey();

        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/orders', ['sku' => $product->sku])
                ->assertCreated()
                ->assertJsonPath('status', 'created');
        }
    }

    public function test_endurance_of_me_endpoint(): void
    {
        $this->actingAsApi();

        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/me')->assertOk();
        }
    }
}
