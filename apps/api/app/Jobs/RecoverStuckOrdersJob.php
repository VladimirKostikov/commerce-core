<?php

namespace App\Jobs;

use App\Contracts\StuckOrderRecoveryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecoverStuckOrdersJob implements ShouldQueue
{
    use Queueable;

    public function handle(StuckOrderRecoveryInterface $recovery): void
    {
        $recovery->recoverAll();
    }
}
