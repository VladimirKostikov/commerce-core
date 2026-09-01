<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tests\Support\ClickHouseTestResults;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class ClickHouseTestResultsSchemaTest extends TestCase
{
    public function test_test_results_table_exists(): void
    {
        $this->assertTrue(ClickHouseTestResults::ensureTable());

        $query = rawurlencode('EXISTS TABLE logs.test_results');
        $response = HttpProbe::get(InfrastructureHost::clickHouseUrl().'/?query='.$query);

        $this->assertSame(200, $response['status']);
        $this->assertSame('1', trim($response['body']));
    }
}
