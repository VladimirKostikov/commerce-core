<?php

namespace App\Services\Catalog;

final class StorefrontStockQuery
{
    public const SQL = 'SELECT sku, name, price, currency, type, image, available_keys_count FROM products WHERE is_active = true AND available_keys_count > 0 ORDER BY available_keys_count DESC, sku LIMIT 50';
}
