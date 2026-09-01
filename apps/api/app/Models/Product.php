<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\ProductType;
use App\Support\NonNegativeMoney;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku',
    'name',
    'type',
    'price',
    'currency',
    'image',
    'is_active',
    'available_keys_count',
])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'currency' => Currency::class,
            'price' => 'integer',
            'is_active' => 'boolean',
            'available_keys_count' => 'integer',
        ];
    }

    public function keys(): HasMany
    {
        return $this->hasMany(ProductKey::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function sku(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => strtoupper(trim($value)),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => trim($value),
        );
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            set: fn (int $value): int => NonNegativeMoney::of($value),
        );
    }
}
