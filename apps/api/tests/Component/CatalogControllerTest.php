<?php

namespace Tests\Component;

use App\Contracts\CatalogStorefrontInterface;
use App\Dto\CatalogItem;
use App\Http\Controllers\CatalogController;
use App\Http\Requests\CatalogIndexRequest;
use App\Models\Product;
use App\Services\Catalog\ProductFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_items_from_service(): void
    {
        $catalog = $this->createMock(CatalogStorefrontInterface::class);
        $catalog->expects($this->once())
            ->method('inStock')
            ->with(50)
            ->willReturn([[
                'sku' => 'HOT-SKU',
                'name' => 'Hot',
                'price' => 500,
                'currency' => 'RUB',
                'type' => 'key',
                'available_keys_count' => 3,
                'image' => null,
            ]]);

        $request = CatalogIndexRequest::create('/api/catalog', 'GET');
        $request->setContainer($this->app);
        $request->validateResolved();

        $response = (new CatalogController())->index($request, $catalog);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('HOT-SKU', $response->getData(true)['items'][0]['sku']);
    }

    public function test_show_returns_catalog_item(): void
    {
        $product = Product::factory()->create([
            'sku' => 'STEAM-TOPUP-500',
            'name' => 'Steam',
            'price' => 500,
            'currency' => 'RUB',
            'type' => 'topup',
            'available_keys_count' => 4,
            'image' => 'assets/steam.png',
            'is_active' => true,
        ]);

        $response = (new CatalogController())->show('STEAM-TOPUP-500', $this->app->make(ProductFinder::class));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(CatalogItem::fromModel($product)->toArray(), $response->getData(true));
    }
}
