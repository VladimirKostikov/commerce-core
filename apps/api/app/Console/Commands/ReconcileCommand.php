<?php

namespace App\Console\Commands;

use App\Contracts\ReconciliationServiceInterface;
use Illuminate\Console\Command;

final class ReconcileCommand extends Command
{
    protected $signature = 'commerce:reconcile';

    protected $description = 'Report paid-not-delivered and delivered-not-paid orders';

    public function handle(ReconciliationServiceInterface $reconciliation): int
    {
        $this->line(json_encode($reconciliation->report()->toArray(), JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
