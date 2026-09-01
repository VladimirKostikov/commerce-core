<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\GrafanaProbe;

final class PhpunitTestsDashboardLiveTest extends TestCase
{
    public function test_phpunit_dashboard_is_loaded_in_grafana(): void
    {
        $response = GrafanaProbe::get('/api/dashboards/uid/phpunit-tests');
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status'], $response['body']);
        $this->assertIsArray($payload);
        $this->assertSame('PHPUnit tests', $payload['dashboard']['title'] ?? null);

        $titles = array_column($payload['dashboard']['panels'] ?? [], 'title');

        $this->assertContains('Pyramid by suite', $titles);
        $this->assertContains('Suite totals', $titles);
        $this->assertContains('Прогон', $titles);
        $this->assertContains('Every test', $titles);
        $this->assertContains('Run suites', $titles);
        $this->assertContains('Failures', $titles);

        $encoded = json_encode($payload['dashboard'], JSON_UNESCAPED_UNICODE);
        $this->assertIsString($encoded);
        $this->assertStringContainsString('logs.test_cases', $encoded);
        $this->assertStringContainsString('argMax', $encoded);
        $this->assertStringContainsString('tests\\/run', $encoded);
        $this->assertStringContainsString('tests\\/log', $encoded);
        $this->assertStringContainsString('прогнать', $encoded);

        foreach (['Unit', 'Component', 'Feature', 'Api', 'Functional', 'Database', 'Integration', 'System', 'E2E', 'Performance'] as $suite) {
            $this->assertStringContainsString($suite, $encoded);
        }

        $linkTitles = array_column($payload['dashboard']['links'] ?? [], 'title');
        $this->assertContains('прогнать all', $linkTitles);
        $this->assertContains('прогнать Component', $linkTitles);
        $this->assertContains('прогнать Database', $linkTitles);
        $this->assertContains('прогнать System', $linkTitles);
    }
}
