<?php

namespace App\Dto;

use App\Enums\Currency;
use App\Enums\ProductType;
use App\Models\Product;

final readonly class CatalogItem
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $price,
        public string $currency,
        public string $type,
        public int $availableKeysCount,
        public ?string $image,
    ) {
    }

    public static function fromModel(Product $product): self
    {
        $currency = $product->currency instanceof Currency
            ? $product->currency->value
            : (string) $product->currency;
        $type = $product->type instanceof ProductType
            ? $product->type->value
            : (string) $product->type;

        return new self(
            $product->sku,
            $product->name,
            $product->price,
            $currency,
            $type,
            $product->available_keys_count,
            $product->image,
        );
    }

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => $this->price,
            'currency' => $this->currency,
            'type' => $this->type,
            'available_keys_count' => $this->availableKeysCount,
            'image' => $this->image,
        ];
    }
}
