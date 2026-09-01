<?php

return [
    'driver' => env('MESSAGING_DRIVER', 'live'),
    'sync_side_effects' => filter_var(env('MESSAGING_SYNC', false), FILTER_VALIDATE_BOOLEAN),
    'kafka' => [
        'brokers' => env('KAFKA_BROKERS', 'kafka:29092'),
        'topic' => env('KAFKA_TOPIC', 'commerce.events'),
        'group' => env('KAFKA_GROUP', 'commerce-api'),
        'timeout_ms' => (int) env('KAFKA_TIMEOUT_MS', 3000),
    ],
    'rabbitmq' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => (int) env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost' => env('RABBITMQ_VHOST', '/'),
        'queue' => env('RABBITMQ_QUEUE', 'commerce.notices'),
        'timeout' => (float) env('RABBITMQ_TIMEOUT', 3),
    ],
    'redis' => [
        'notices_key' => env('COMMERCE_NOTICES_KEY', 'commerce:notices'),
        'events_key' => env('COMMERCE_EVENTS_KEY', 'commerce:events:inbox'),
        'last_event_key' => env('COMMERCE_LAST_EVENT_KEY', 'commerce:events:last'),
    ],
];
