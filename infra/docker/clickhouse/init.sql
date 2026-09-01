CREATE DATABASE IF NOT EXISTS logs;

CREATE TABLE IF NOT EXISTS logs.app_logs
(
    event_time DateTime DEFAULT now(),
    level LowCardinality(String),
    channel LowCardinality(String),
    message String,
    context String
)
ENGINE = MergeTree
PARTITION BY toYYYYMM(event_time)
ORDER BY (event_time, level);

CREATE TABLE IF NOT EXISTS logs.http_metrics
(
    event_time DateTime DEFAULT now(),
    path LowCardinality(String),
    status UInt16,
    duration_ms Float64
)
ENGINE = MergeTree
PARTITION BY toYYYYMM(event_time)
ORDER BY (event_time, path);

CREATE TABLE IF NOT EXISTS logs.commerce_events
(
    event_time DateTime DEFAULT now(),
    channel LowCardinality(String),
    event LowCardinality(String),
    order_id String,
    reference String,
    status LowCardinality(String),
    message String,
    context String
)
ENGINE = MergeTree
PARTITION BY toYYYYMM(event_time)
ORDER BY (event_time, channel, order_id);

CREATE TABLE IF NOT EXISTS logs.test_results
(
    event_time DateTime DEFAULT now(),
    run_id LowCardinality(String),
    suite LowCardinality(String),
    test_class String,
    test_name String,
    status LowCardinality(String),
    duration_ms Float64,
    message String
)
ENGINE = MergeTree
PARTITION BY toYYYYMM(event_time)
ORDER BY (event_time, suite, status);

CREATE TABLE IF NOT EXISTS logs.test_cases
(
    suite LowCardinality(String),
    test_class String,
    test_name String
)
ENGINE = MergeTree
ORDER BY (test_class, test_name);
