<?php

namespace App\Console\Commands;

use App\Contracts\TestCatalogSyncInterface;
use Illuminate\Console\Command;

final class SyncTestCatalogCommand extends Command
{
    protected $signature = 'commerce:sync-test-catalog';

    protected $description = 'Write the PHPUnit catalog into ClickHouse for Grafana';

    public function handle(TestCatalogSyncInterface $sync): int
    {
        $this->info((string) $sync->sync());

        return self::SUCCESS;
    }
}
