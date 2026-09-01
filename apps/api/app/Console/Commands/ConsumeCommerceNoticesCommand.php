<?php

namespace App\Console\Commands;

use App\Contracts\CommerceMessageProcessorInterface;
use Illuminate\Console\Command;

final class ConsumeCommerceNoticesCommand extends Command
{
    protected $signature = 'commerce:consume-notices {--max=10}';

    protected $description = 'Consume delivery notices from RabbitMQ into Redis';

    public function handle(CommerceMessageProcessorInterface $processor): int
    {
        $this->info((string) $processor->consumeNotices((int) $this->option('max')));

        return self::SUCCESS;
    }
}
