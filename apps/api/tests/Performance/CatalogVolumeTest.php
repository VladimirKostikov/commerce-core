<?php

namespace Tests\Performance;

use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\ProductKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_volume_of_unique_keys_can_be_stored(): void
    {
        $product = Product::factory()->create();
        ProductKey::factory()->count(200)->create([
            'product_id' => $product->id,
        ]);

        $this->assertSame(200, ProductKey::query()->where('product_id', $product->id)->count());
        $this->assertSame(200, ProductKey::query()->pluck('code')->unique()->count());
    }

    public function test_spike_of_payment_events_keeps_unique_event_ids(): void
    {
        PaymentEvent::factory()->count(40)->create();

        $this->assertSame(40, PaymentEvent::query()->count());
        $this->assertSame(40, PaymentEvent::query()->pluck('event_id')->unique()->count());
    }
}
