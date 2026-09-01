<?php

namespace Tests\Support;

final class ClickHouseTestResults
{
    public const CREATE_TABLE_SQL = <<<'SQL'
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
ORDER BY (event_time, suite, status)
SQL;

    public static function suiteOf(string $class): string
    {
        $parts = explode('\\', $class);

        return $parts[1] ?? 'Unknown';
    }

    public static function ensureTable(): bool
    {
        $response = HttpProbe::post(InfrastructureHost::clickHouseUrl().'/', self::CREATE_TABLE_SQL, 10);

        return $response['status'] === 200;
    }

    public static function insertRows(array $rows): bool
    {
        if ($rows === []) {
            return true;
        }

        if (! self::ensureTable()) {
            return false;
        }

        $body = implode("\n", array_map(
            static fn (array $row): string => json_encode($row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $rows,
        ));
        $sql = 'INSERT INTO logs.test_results FORMAT JSONEachRow '.$body;
        $response = HttpProbe::post(InfrastructureHost::clickHouseUrl().'/', $sql, 15);

        return $response['status'] === 200;
    }

    public static function countWhere(string $where): int
    {
        $sql = 'SELECT count() FROM logs.test_results WHERE '.$where;
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.rawurlencode($sql));

        if ($response['status'] !== 200) {
            return 0;
        }

        return (int) trim($response['body']);
    }
}
