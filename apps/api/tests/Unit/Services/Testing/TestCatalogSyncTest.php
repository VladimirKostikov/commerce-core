<?php

namespace Tests\Unit\Services\Testing;

use App\Contracts\ClickHouseClientInterface;
use App\Contracts\TestCatalogInterface;
use App\Services\Testing\TestCatalogSync;
use Tests\TestCase;

final class TestCatalogSyncTest extends TestCase
{
    public function test_writes_cases(): void
    {
        $catalog = $this->createMock(TestCatalogInterface::class);
        $catalog->method('cases')->willReturn([
            ['suite' => 'Unit', 'class' => 'Tests\\Unit\\FooTest', 'method' => 'test_foo'],
        ]);

        $clickHouse = $this->createMock(ClickHouseClientInterface::class);
        $clickHouse->expects($this->exactly(2))->method('execute');
        $clickHouse->expects($this->once())->method('insertJsonRows')->with(
            'logs.test_cases',
            [['suite' => 'Unit', 'test_class' => 'Tests\\Unit\\FooTest', 'test_name' => 'test_foo']],
        );

        $this->assertSame(1, (new TestCatalogSync($catalog, $clickHouse))->sync());
    }

    public function test_down_clickhouse_returns_zero(): void
    {
        $catalog = $this->createMock(TestCatalogInterface::class);
        $catalog->method('cases')->willReturn([['suite' => 'Unit', 'class' => 'A', 'method' => 'b']]);
        $clickHouse = $this->createMock(ClickHouseClientInterface::class);
        $clickHouse->method('execute')->willThrowException(new \RuntimeException('down'));

        $this->assertSame(0, (new TestCatalogSync($catalog, $clickHouse))->sync());
    }
}
