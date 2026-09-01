<?php

namespace App\Http\Controllers;

use App\Contracts\OrderServiceInterface;
use App\Dto\OrderData;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, OrderServiceInterface $orders): JsonResponse
    {
        $order = $orders->create($this->authenticatedUser($request), $request->string('sku')->toString());

        return response()->json(OrderData::fromModel($order)->toArray(), 201);
    }

    public function show(Request $request, string $id, OrderServiceInterface $orders): JsonResponse
    {
        $order = $orders->findForUser($this->authenticatedUser($request), $id);

        return response()->json(OrderData::fromModel($order)->toArray());
    }
}
