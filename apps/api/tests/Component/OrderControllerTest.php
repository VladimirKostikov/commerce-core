<?php

namespace Tests\Component;

use App\Contracts\OrderServiceInterface;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Http\Controllers\OrderController;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

final class OrderControllerTest extends TestCase
{
    public function test_store_returns_created_order_json(): void
    {
        $user = new User();
        $user->id = 7;
        $order = new Order();
        $order->id = 'ord_testcontroller';
        $order->sku = 'STEAM-TOPUP-500';
        $order->amount = 500;
        $order->currency = Currency::Rub;
        $order->status = OrderStatus::Created;
        $order->delivery_code = null;

        $orders = $this->createMock(OrderServiceInterface::class);
        $orders->method('create')->willReturn($order);

        $request = CreateOrderRequest::create('/api/orders', 'POST', ['sku' => 'STEAM-TOPUP-500']);
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $user);
        $request->validateResolved();

        $response = (new OrderController())->store($request, $orders);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('ord_testcontroller', $response->getData(true)['id']);
        $this->assertSame('created', $response->getData(true)['status']);
    }

    public function test_show_returns_order_json(): void
    {
        $user = new User();
        $user->id = 7;
        $order = new Order();
        $order->id = 'ord_show';
        $order->sku = 'STEAM-TOPUP-500';
        $order->amount = 500;
        $order->currency = Currency::Rub;
        $order->status = OrderStatus::Delivered;
        $order->delivery_code = 'LFXC-TNCS-BPCD';

        $orders = $this->createMock(OrderServiceInterface::class);
        $orders->method('findForUser')->willReturn($order);

        $request = Request::create('/api/orders/ord_show', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new OrderController())->show($request, 'ord_show', $orders);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('LFXC-TNCS-BPCD', $response->getData(true)['delivery_code']);
    }
}
