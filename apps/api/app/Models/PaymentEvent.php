<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Support\NonNegativeMoney;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id',
    'order_id',
    'status',
    'amount',
    'currency',
    'occurred_at',
    'payload',
    'processed_at',
])]
class PaymentEvent extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'currency' => Currency::class,
            'amount' => 'integer',
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function eventId(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => trim($value),
        );
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            set: fn (int $value): int => NonNegativeMoney::of($value),
        );
    }
}
