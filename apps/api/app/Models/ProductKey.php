<?php

namespace App\Models;

use App\Enums\ProductKeyStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'code',
    'status',
    'order_id',
])]
class ProductKey extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ProductKeyStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => strtoupper(trim($value)),
        );
    }
}
