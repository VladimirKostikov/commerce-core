<?php

namespace App\Console\Commands;

use App\Contracts\CommerceMessageProcessorInterface;
use Illuminate\Console\Command;

final class ConsumeCommerceEventsCommand extends Command
{
    protected $signature = 'commerce:consume-events {--max=10} {--timeout=3000}';

    protected $description = 'Consume commerce facts from Kafka into Redis';

    public function handle(CommerceMessageProcessorInterface $processor): int
    {
        $this->info((string) $processor->consumeEvents(
            (int) $this->option('max'),
            (int) $this->option('timeout'),
        ));

        return self::SUCCESS;
    }
}
