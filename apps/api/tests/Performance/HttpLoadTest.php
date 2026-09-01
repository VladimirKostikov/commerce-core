<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\ClickHouseMetrics;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class HttpLoadTest extends TestCase
{
    public function test_volume_of_home_requests_succeed(): void
    {
        $this->assertSame(50, $this->successfulGets('/', 50));
    }

    public function test_spike_of_health_requests_succeed(): void
    {
        $this->assertSame(20, $this->successfulGets('/api/health', 20));
    }

    public function test_endurance_of_up_endpoint(): void
    {
        $startedAt = microtime(true);
        $ok = $this->successfulGets('/up', 100);
        $elapsed = microtime(true) - $startedAt;

        $this->assertSame(100, $ok);
        $this->assertLessThan(30, $elapsed);
    }

    public function test_scalability_keeps_latency_bounded(): void
    {
        $durations = [];

        for ($i = 0; $i < 30; $i++) {
            $startedAt = microtime(true);
            $response = HttpProbe::get(InfrastructureHost::appUrl().'/up');
            $elapsed = microtime(true) - $startedAt;
            $durations[] = $elapsed;
            ClickHouseMetrics::recordHttp('/up', $response['status'], $elapsed * 1000);

            $this->assertSame(200, $response['status']);
        }

        sort($durations);
        $p95Index = (int) floor(count($durations) * 0.95) - 1;

        $this->assertLessThan(2.0, $durations[max(0, $p95Index)]);
    }

    private function successfulGets(string $path, int $times): int
    {
        $ok = 0;
        $url = InfrastructureHost::appUrl().$path;

        for ($i = 0; $i < $times; $i++) {
            $startedAt = microtime(true);
            $response = HttpProbe::get($url);
            ClickHouseMetrics::recordHttp($path, $response['status'], (microtime(true) - $startedAt) * 1000);

            if ($response['status'] === 200) {
                $ok++;
            }
        }

        return $ok;
    }
}
