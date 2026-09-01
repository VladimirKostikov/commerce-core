<?php

namespace App\Services\Messaging;

use Closure;
use Illuminate\Support\Facades\DB;

final class SideEffectScheduler
{
    public function run(Closure $callback): void
    {
        if ((bool) config('messaging.sync_side_effects', false)) {
            $callback();

            return;
        }

        DB::afterCommit($callback);
    }
}
