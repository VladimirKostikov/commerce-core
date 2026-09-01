<?php

namespace App\Services\Testing;

use App\Contracts\ClickHouseClientInterface;
use App\Contracts\TestCatalogInterface;
use App\Contracts\TestCatalogSyncInterface;
use Throwable;

final class TestCatalogSync implements TestCatalogSyncInterface
{
    public function __construct(
        private readonly TestCatalogInterface $catalog,
        private readonly ClickHouseClientInterface $clickHouse,
    ) {
    }

    public function sync(): int
    {
        $rows = [];

        foreach ($this->catalog->cases() as $case) {
            $rows[] = [
                'suite' => $case['suite'],
                'test_class' => $case['class'],
                'test_name' => $case['method'],
            ];
        }

        try {
            $this->clickHouse->execute(
                'CREATE TABLE IF NOT EXISTS logs.test_cases (suite LowCardinality(String), test_class String, test_name String) ENGINE = MergeTree ORDER BY (test_class, test_name)',
            );
            $this->clickHouse->execute('TRUNCATE TABLE logs.test_cases');
            $this->clickHouse->insertJsonRows('logs.test_cases', $rows);
        } catch (Throwable) {
            return 0;
        }

        return count($rows);
    }
}
