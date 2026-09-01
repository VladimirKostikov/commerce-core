<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class CatalogStorefrontHttpLoadTest extends TestCase
{
    public function test_spike_of_storefront_requests_succeed(): void
    {
        $ok = 0;
        $url = InfrastructureHost::appUrl().'/api/catalog?limit=20';

        for ($i = 0; $i < 40; $i++) {
            $response = HttpProbe::get($url);

            if ($response['status'] === 200) {
                $ok++;
            }
        }

        $this->assertSame(40, $ok);
    }

    public function test_endurance_of_storefront_requests_succeed(): void
    {
        $ok = 0;
        $url = InfrastructureHost::appUrl().'/api/catalog?limit=20';
        $startedAt = microtime(true);

        for ($i = 0; $i < 80; $i++) {
            $response = HttpProbe::get($url);

            if ($response['status'] === 200) {
                $ok++;
            }
        }

        $this->assertSame(80, $ok);
        $this->assertLessThan(30, microtime(true) - $startedAt);
    }

    public function test_scalability_keeps_storefront_latency_bounded(): void
    {
        $url = InfrastructureHost::appUrl().'/api/catalog?limit=20';
        $durations = [];

        for ($i = 0; $i < 30; $i++) {
            $startedAt = microtime(true);
            $response = HttpProbe::get($url);
            $durations[] = microtime(true) - $startedAt;

            $this->assertSame(200, $response['status']);
        }

        sort($durations);
        $p95Index = (int) floor(count($durations) * 0.95) - 1;

        $this->assertLessThan(2.0, $durations[max(0, $p95Index)]);
    }

    public function test_parallel_storefront_requests_succeed(): void
    {
        $url = InfrastructureHost::appUrl().'/api/catalog?limit=20';
        $count = 25;
        $command = sprintf(
            'for i in $(seq 1 %d); do curl -s -o /dev/null -w "%%{http_code}\\n" --max-time 5 %s & done; wait',
            $count,
            escapeshellarg($url)
        );

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $this->assertCount($count, $output);

        foreach ($output as $status) {
            $this->assertSame('200', trim($status));
        }
    }
}
