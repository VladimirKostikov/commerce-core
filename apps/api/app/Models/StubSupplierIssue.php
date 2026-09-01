<?php

namespace App\Models;

use App\Enums\SupplierName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'supplier',
    'request_id',
    'sku',
    'order_id',
    'code',
])]
class StubSupplierIssue extends Model
{
    protected function casts(): array
    {
        return [
            'supplier' => SupplierName::class,
        ];
    }
}
