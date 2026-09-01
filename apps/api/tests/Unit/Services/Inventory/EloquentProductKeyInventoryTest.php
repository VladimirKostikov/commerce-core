<?php

namespace Tests\Unit\Services\Inventory;

use App\Enums\ProductKeyStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductKey;
use App\Services\Inventory\EloquentProductKeyInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class EloquentProductKeyInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_assigns_one_key_to_order(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $inventory = $this->app->make(EloquentProductKeyInventory::class);

        $key = $inventory->claim($product->id, $order->id);

        $this->assertNotNull($key);
        $this->assertSame($order->id, $key->order_id);
        $this->assertSame(ProductKeyStatus::Sold, $key->status);
        $this->assertSame(0, $product->fresh()?->available_keys_count);
        $this->assertTrue($inventory->claim($product->id, $order->id)->is($key));

        $other = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $this->assertNull($inventory->claim($product->id, $other->id));
    }

    public function test_claim_decrements_available_keys_count(): void
    {
        $product = Product::factory()->create(['available_keys_count' => 2]);
        ProductKey::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => ProductKeyStatus::Available,
        ]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->app->make(EloquentProductKeyInventory::class)->claim($product->id, $order->id);

        $this->assertSame(1, $product->fresh()?->available_keys_count);
    }
}
