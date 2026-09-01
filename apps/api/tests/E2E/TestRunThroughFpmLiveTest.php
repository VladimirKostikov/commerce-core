<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class TestRunThroughFpmLiveTest extends TestCase
{
    public function test_fpm_run_uses_cli_php(): void
    {
        $url = InfrastructureHost::appUrl()
            .'/api/tests/run?class='.rawurlencode('Tests\\Unit\\Services\\Testing\\PhpCliBinaryTest')
            .'&method=test_path_is_cli_not_fpm';
        $response = HttpProbe::get($url, 60, ['Accept: text/plain']);

        $this->assertSame(200, $response['status'], $response['body']);
        $this->assertStringNotContainsString('{', $response['body']);
        $this->assertStringNotContainsString('php-fpm', $response['body']);
        $this->assertStringContainsString('ok', $response['body']);
        $this->assertStringContainsString('PASS', $response['body']);
    }
}
