<?php

namespace Tests\Unit\Support;

use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use Tests\Support\ClickHouseTestResults;
use Tests\Support\PhpUnit\ClickHouseTestCollector;

final class ClickHouseTestResultsTest extends TestCase
{
    public function test_suite_is_taken_from_tests_namespace(): void
    {
        $this->assertSame('Unit', ClickHouseTestResults::suiteOf('Tests\\Unit\\Services\\FooTest'));
        $this->assertSame('E2E', ClickHouseTestResults::suiteOf('Tests\\E2E\\LiveStackTest'));
        $this->assertSame('Performance', ClickHouseTestResults::suiteOf('Tests\\Performance\\HttpLoadTest'));
        $this->assertSame('Unknown', ClickHouseTestResults::suiteOf('Foo'));
    }

    public function test_collector_records_suite_status_and_duration(): void
    {
        $collector = new ClickHouseTestCollector('run_unit');
        $test = new TestMethod(
            'Tests\\Feature\\CatalogStorefrontTest',
            'test_storefront_lists_in_stock_and_fetches_sku',
            __FILE__,
            10,
            new TestDox('CatalogStorefrontTest', 'storefront lists', 'storefront lists'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );

        $collector->markStarted($test);
        usleep(1000);
        $collector->record($test, 'passed');

        $row = $collector->pendingRows()[0];

        $this->assertSame('run_unit', $row['run_id']);
        $this->assertSame('Feature', $row['suite']);
        $this->assertSame('Tests\\Feature\\CatalogStorefrontTest', $row['test_class']);
        $this->assertSame('test_storefront_lists_in_stock_and_fetches_sku', $row['test_name']);
        $this->assertSame('passed', $row['status']);
        $this->assertGreaterThan(0, $row['duration_ms']);
        $this->assertSame('', $row['message']);
    }
}
