<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\InfrastructureHost;

final class ConcurrentSpikeTest extends TestCase
{
    public function test_parallel_up_requests_all_succeed(): void
    {
        $url = InfrastructureHost::appUrl().'/up';
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
