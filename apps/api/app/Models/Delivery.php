<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\SupplierName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'request_id',
    'supplier',
    'code',
    'status',
])]
class Delivery extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'supplier' => SupplierName::class,
            'status' => DeliveryStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function requestId(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => trim($value),
        );
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : strtoupper(trim($value)),
        );
    }
}
