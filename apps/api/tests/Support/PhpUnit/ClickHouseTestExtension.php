<?php

namespace Tests\Support\PhpUnit;

use PHPUnit\Event\Application\Finished as ApplicationFinished;
use PHPUnit\Event\Application\FinishedSubscriber as ApplicationFinishedSubscriber;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\MarkedIncompleteSubscriber;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedSubscriber;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class ClickHouseTestExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $collector = new ClickHouseTestCollector(bin2hex(random_bytes(8)));

        $facade->registerSubscribers(
            new class($collector) implements PreparedSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(Prepared $event): void
                {
                    $this->collector->markStarted($event->test());
                }
            },
            new class($collector) implements PassedSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(Passed $event): void
                {
                    $this->collector->record($event->test(), 'passed');
                }
            },
            new class($collector) implements FailedSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(Failed $event): void
                {
                    $this->collector->record($event->test(), 'failed', $event->throwable()->message());
                }
            },
            new class($collector) implements ErroredSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(Errored $event): void
                {
                    $this->collector->record($event->test(), 'errored', $event->throwable()->message());
                }
            },
            new class($collector) implements SkippedSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(Skipped $event): void
                {
                    $this->collector->record($event->test(), 'skipped', $event->message());
                }
            },
            new class($collector) implements MarkedIncompleteSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(MarkedIncomplete $event): void
                {
                    $this->collector->record($event->test(), 'incomplete', $event->throwable()->message());
                }
            },
            new class($collector) implements ApplicationFinishedSubscriber
            {
                public function __construct(private ClickHouseTestCollector $collector)
                {
                }

                public function notify(ApplicationFinished $event): void
                {
                    $this->collector->flush();
                }
            },
        );
    }
}
