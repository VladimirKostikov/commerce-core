<?php

namespace App\Http\Controllers;

use App\Contracts\CatalogStorefrontInterface;
use App\Dto\CatalogItem;
use App\Http\Requests\CatalogIndexRequest;
use App\Services\Catalog\ProductFinder;
use Illuminate\Http\JsonResponse;

final class CatalogController extends Controller
{
    public function index(CatalogIndexRequest $request, CatalogStorefrontInterface $catalog): JsonResponse
    {
        return response()->json([
            'items' => $catalog->inStock($request->limit()),
        ]);
    }

    public function show(string $sku, ProductFinder $products): JsonResponse
    {
        return response()->json(CatalogItem::fromModel($products->activeBySku($sku))->toArray());
    }
}
