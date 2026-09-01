<?php

namespace App\Console\Commands;

use App\Contracts\StuckOrderRecoveryInterface;
use Illuminate\Console\Command;

final class RecoverStuckOrdersCommand extends Command
{
    protected $signature = 'commerce:recover-stuck';

    protected $description = 'Retry stale paid and in-flight orders';

    public function handle(StuckOrderRecoveryInterface $recovery): int
    {
        $this->info((string) $recovery->recoverAll());

        return self::SUCCESS;
    }
}
