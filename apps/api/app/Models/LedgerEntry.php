<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\LedgerDirection;
use App\Enums\LedgerReason;
use App\Support\NonNegativeMoney;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'direction',
    'amount',
    'currency',
    'reason',
])]
class LedgerEntry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'direction' => LedgerDirection::class,
            'reason' => LedgerReason::class,
            'currency' => Currency::class,
            'amount' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            set: fn (int $value): int => NonNegativeMoney::of($value),
        );
    }

    protected function reason(): Attribute
    {
        return Attribute::make(
            set: fn (LedgerReason|string $value): string => $value instanceof LedgerReason
                ? $value->value
                : trim($value),
        );
    }
}
