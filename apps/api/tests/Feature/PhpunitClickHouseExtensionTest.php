<?php

namespace Tests\Feature;

use Tests\TestCase;

final class PhpunitClickHouseExtensionTest extends TestCase
{
    public function test_phpunit_xml_bootstraps_clickhouse_reporter(): void
    {
        $xml = (string) file_get_contents(base_path('phpunit.xml'));

        $this->assertStringContainsString('Tests\\Support\\PhpUnit\\ClickHouseTestExtension', $xml);
    }
}
