<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_get_order(): void
    {
        $user = $this->actingAsApi();
        $product = CommerceCatalog::productWithAvailableKey();

        $created = $this->postJson('/api/orders', ['sku' => $product->sku])
            ->assertCreated()
            ->assertJsonPath('sku', $product->sku)
            ->assertJsonPath('amount', $product->price)
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('delivery_code', null);

        $id = $created->json('id');
        $this->assertIsString($id);
        $this->assertStringStartsWith('ord_', $id);

        $this->getJson('/api/orders/'.$id)
            ->assertOk()
            ->assertJsonPath('id', $id)
            ->assertJsonPath('status', 'created');

        $this->assertSame($user->id, Order::query()->find($id)?->user_id);
    }

    public function test_cannot_read_another_users_order(): void
    {
        $this->actingAsApi();
        $foreign = Order::factory()->create();

        $this->getJson('/api/orders/'.$foreign->id)->assertNotFound();
    }

    public function test_unknown_sku_returns_not_found(): void
    {
        $this->actingAsApi();

        $this->postJson('/api/orders', ['sku' => 'NO-SUCH-SKU'])->assertNotFound();
    }

    public function test_guest_cannot_create_order(): void
    {
        CommerceCatalog::productWithAvailableKey();

        $this->postJson('/api/orders', ['sku' => 'STEAM-TOPUP-500'])->assertUnauthorized();
    }
}
