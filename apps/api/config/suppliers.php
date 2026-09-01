<?php

return [
    'timeout_seconds' => (float) env('SUPPLIER_TIMEOUT_SECONDS', 2),
    'retries' => (int) env('SUPPLIER_RETRIES', 3),
    'backoff_ms' => (int) env('SUPPLIER_BACKOFF_MS', 100),
    'a' => [
        'url' => env('SUPPLIER_A_URL', 'http://nginx/api/stub/suppliers/a/issue'),
        'mode' => env('SUPPLIER_A_MODE', 'ok'),
    ],
    'b' => [
        'url' => env('SUPPLIER_B_URL', 'http://nginx/api/stub/suppliers/b/issue'),
        'mode' => env('SUPPLIER_B_MODE', 'ok'),
    ],
];
