<?php

namespace Tests\Api;

use Tests\TestCase;

final class PhpunitReporterContractTest extends TestCase
{
    public function test_phpunit_extension_bootstrap_contract(): void
    {
        $xml = simplexml_load_string((string) file_get_contents(base_path('phpunit.xml')));

        $this->assertNotFalse($xml);
        $this->assertSame(
            'Tests\\Support\\PhpUnit\\ClickHouseTestExtension',
            (string) $xml->extensions->bootstrap['class'],
        );
    }
}
