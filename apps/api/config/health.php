<?php

return [
    'timeout' => (float) env('HEALTH_TIMEOUT', 2),
    'rabbitmq' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => (int) env('RABBITMQ_PORT', 5672),
    ],
    'kafka' => [
        'host' => env('KAFKA_HOST', 'kafka'),
        'port' => (int) env('KAFKA_PORT', 29092),
    ],
    'clickhouse' => [
        'scheme' => env('CLICKHOUSE_SCHEME', 'http'),
        'host' => env('CLICKHOUSE_HOST', 'clickhouse'),
        'port' => (int) env('CLICKHOUSE_PORT', 8123),
    ],
];
