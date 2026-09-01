<?php

return [
    'storefront_limit' => (int) env('CATALOG_STOREFRONT_LIMIT', 50),
    'storefront_max' => (int) env('CATALOG_STOREFRONT_MAX', 100),
    'cache_ttl' => (int) env('CATALOG_CACHE_TTL', 10),
];

