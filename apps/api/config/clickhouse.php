<?php

return [
    'scheme' => env('CLICKHOUSE_SCHEME', 'http'),
    'host' => env('CLICKHOUSE_HOST', 'clickhouse'),
    'port' => (int) env('CLICKHOUSE_PORT', 8123),
    'database' => env('CLICKHOUSE_DATABASE', 'logs'),
    'user' => env('CLICKHOUSE_USER', 'default'),
    'password' => env('CLICKHOUSE_PASSWORD', ''),
    'timeout' => (float) env('CLICKHOUSE_TIMEOUT', 2),
];
