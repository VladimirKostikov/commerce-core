<?php

namespace Tests\Unit\Services\ClickHouse;

use App\Dto\CommerceLog;
use App\Services\ClickHouse\ClickHouseHttpClient;
use App\Services\Logging\ClickHouseCommerceLogger;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ClickHouseCommerceLoggerTest extends TestCase
{
    public function test_record_posts_json_each_row(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $logger = new ClickHouseCommerceLogger($this->app->make(ClickHouseHttpClient::class));
        $logger->record(new CommerceLog(
            'payment',
            'accepted',
            'ord_log_1',
            'evt_1',
            'paid',
            'payment accepted',
            ['amount' => 500],
        ));

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), 'commerce_events')
                && str_contains($request->body(), '"order_id":"ord_log_1"')
                && str_contains($request->body(), '"channel":"payment"');
        });
    }

    public function test_record_swallows_transport_errors(): void
    {
        Http::fake([
            '*' => Http::response('fail', 500),
        ]);

        $logger = new ClickHouseCommerceLogger($this->app->make(ClickHouseHttpClient::class));
        $logger->record(new CommerceLog('delivery', 'timeout', 'ord_x', 'req_x', 'delivering', 'timeout'));

        $this->assertTrue(true);
    }
}
