<?php

namespace Tests\Component;

use App\Http\Controllers\StubSupplierController;
use App\Http\Requests\StubIssueRequest;
use App\Models\Order;
use App\Services\Suppliers\StubIssueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CommerceCatalog;
use Tests\TestCase;

final class StubSupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_returns_issued_code(): void
    {
        $product = CommerceCatalog::productWithAvailableKey();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'amount' => $product->price,
        ]);
        $request = StubIssueRequest::create('/api/stub/suppliers/a/issue', 'POST', [
            'request_id' => 'req_c',
            'sku' => $product->sku,
            'order_id' => $order->id,
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $response = (new StubSupplierController())(
            $request,
            'a',
            $this->app->make(StubIssueService::class),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getData(true)['status']);
        $this->assertSame('LFXC-TNCS-BPCD', $response->getData(true)['code']);
    }

    public function test_controller_rejects_unknown_supplier(): void
    {
        $request = StubIssueRequest::create('/api/stub/suppliers/z/issue', 'POST', [
            'request_id' => 'req_c',
            'sku' => 'KEY-GTA5',
            'order_id' => 'ord_c',
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $response = (new StubSupplierController())(
            $request,
            'z',
            $this->app->make(StubIssueService::class),
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
