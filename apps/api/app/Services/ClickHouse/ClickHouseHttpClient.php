<?php

namespace App\Services\ClickHouse;

use App\Contracts\ClickHouseClientInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class ClickHouseHttpClient implements ClickHouseClientInterface
{
    public function insertJson(string $table, array $row): void
    {
        $sql = 'INSERT INTO '.$table.' FORMAT JSONEachRow';
        $body = json_encode($row, JSON_THROW_ON_ERROR);
        $response = $this->post($sql, $body);

        if ($response->failed()) {
            throw new RuntimeException('ClickHouse insert failed: HTTP '.$response->status());
        }
    }

    public function insertJsonRows(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $body = implode("\n", array_map(
            static fn (array $row): string => json_encode($row, JSON_THROW_ON_ERROR),
            $rows,
        ));
        $response = $this->post('INSERT INTO '.$table.' FORMAT JSONEachRow', $body, 15);

        if ($response->failed()) {
            throw new RuntimeException('ClickHouse insert failed: HTTP '.$response->status());
        }
    }

    public function query(string $sql): string
    {
        $response = Http::timeout($this->timeout())
            ->withHeaders($this->authHeaders())
            ->get($this->baseUrl().'/', ['query' => $sql]);

        if ($response->failed()) {
            throw new RuntimeException('ClickHouse query failed: HTTP '.$response->status());
        }

        return trim($response->body());
    }

    public function execute(string $sql): void
    {
        $response = Http::timeout($this->timeout())
            ->withHeaders($this->authHeaders())
            ->withBody($sql, 'text/plain')
            ->post($this->baseUrl().'/');

        if ($response->failed()) {
            throw new RuntimeException('ClickHouse execute failed: HTTP '.$response->status());
        }
    }

    private function post(string $sql, string $body, float $timeout = 0)
    {
        return Http::timeout($timeout > 0 ? $timeout : $this->timeout())
            ->withHeaders($this->authHeaders())
            ->withBody($body, 'application/json')
            ->post($this->baseUrl().'/?query='.rawurlencode($sql));
    }

    private function baseUrl(): string
    {
        return sprintf(
            '%s://%s:%d',
            (string) config('clickhouse.scheme'),
            (string) config('clickhouse.host'),
            (int) config('clickhouse.port'),
        );
    }

    private function authHeaders(): array
    {
        return [
            'X-ClickHouse-User' => (string) config('clickhouse.user'),
            'X-ClickHouse-Key' => (string) config('clickhouse.password'),
        ];
    }

    private function timeout(): float
    {
        return (float) config('clickhouse.timeout', 2);
    }
}
