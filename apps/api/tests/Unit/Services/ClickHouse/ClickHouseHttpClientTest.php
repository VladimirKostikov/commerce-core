<?php

namespace Tests\Unit\Services\ClickHouse;

use App\Services\ClickHouse\ClickHouseHttpClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ClickHouseHttpClientTest extends TestCase
{
    public function test_query_uses_get(): void
    {
        Http::fake(['*' => Http::response('1', 200)]);

        $this->assertSame('1', $this->app->make(ClickHouseHttpClient::class)->query('SELECT 1'));

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_contains($request->url(), 'SELECT'));
    }

    public function test_execute_uses_post(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        $this->app->make(ClickHouseHttpClient::class)->execute('TRUNCATE TABLE logs.test_cases');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && $request->body() === 'TRUNCATE TABLE logs.test_cases');
    }
}
