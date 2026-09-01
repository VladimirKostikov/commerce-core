<?php

namespace Tests\Feature;

use App\Contracts\CatalogStorefrontInterface;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class CatalogCacheBustTest extends TestCase
{
    use RefreshDatabase;

    public function test_claiming_last_key_drops_sku_from_cached_storefront(): void
    {
        Cache::flush();
        $product = CommerceCatalog::productWithAvailableKey('CACHE-SKU');
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);

        $this->assertSame(['CACHE-SKU'], array_column(
            $this->app->make(CatalogStorefrontInterface::class)->inStock(10),
            'sku',
        ));

        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid(
            $order->id,
            'evt_cache_bust',
            $order->amount,
        ))->assertOk();

        $this->getJson('/api/catalog')->assertOk()->assertJsonCount(0, 'items');
    }
}
