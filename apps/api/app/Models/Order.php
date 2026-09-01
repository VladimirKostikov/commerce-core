<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Support\NonNegativeMoney;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'id',
    'user_id',
    'product_id',
    'sku',
    'amount',
    'currency',
    'status',
    'delivery_code',
    'delivery_request_id',
    'lock_version',
])]
class Order extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'currency' => Currency::class,
            'amount' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->id === null || $order->id === '') {
                $order->id = 'ord_'.strtolower((string) Str::ulid());
            }

            if ($order->sku === null && $order->product !== null) {
                $order->sku = $order->product->sku;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function productKeys(): HasMany
    {
        return $this->hasMany(ProductKey::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    protected function sku(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => strtoupper(trim($value)),
        );
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            set: fn (int $value): int => NonNegativeMoney::of($value),
        );
    }

    protected function deliveryCode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : strtoupper(trim($value)),
        );
    }
}
