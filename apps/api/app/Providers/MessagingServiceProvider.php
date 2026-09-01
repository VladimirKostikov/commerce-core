<?php

namespace App\Providers;

use App\Contracts\CommerceEventBusInterface;
use App\Contracts\CommerceInboxInterface;
use App\Contracts\CommerceMessageProcessorInterface;
use App\Contracts\CommerceWorkQueueInterface;
use App\Services\Messaging\CommerceEventCodec;
use App\Services\Messaging\CommerceMessageProcessor;
use App\Services\Messaging\CommerceWorkCodec;
use App\Services\Messaging\KafkaCommerceEventBus;
use App\Services\Messaging\NullCommerceEventBus;
use App\Services\Messaging\NullCommerceInbox;
use App\Services\Messaging\NullCommerceWorkQueue;
use App\Services\Messaging\RabbitMqBroker;
use App\Services\Messaging\RabbitMqCommerceWorkQueue;
use App\Services\Messaging\RedisCommerceInbox;
use Illuminate\Support\ServiceProvider;

final class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommerceEventCodec::class);
        $this->app->singleton(CommerceWorkCodec::class);
        $this->app->singleton(RabbitMqBroker::class);
        $this->app->singleton(CommerceEventBusInterface::class, function ($app): CommerceEventBusInterface {
            if ($this->offDriver($app)) {
                return new NullCommerceEventBus();
            }

            return $app->make(KafkaCommerceEventBus::class);
        });
        $this->app->singleton(CommerceWorkQueueInterface::class, function ($app): CommerceWorkQueueInterface {
            if ($this->offDriver($app)) {
                return new NullCommerceWorkQueue();
            }

            return $app->make(RabbitMqCommerceWorkQueue::class);
        });
        $this->app->singleton(CommerceInboxInterface::class, function ($app): CommerceInboxInterface {
            if ($this->offDriver($app)) {
                return new NullCommerceInbox();
            }

            return $app->make(RedisCommerceInbox::class);
        });
        $this->app->singleton(CommerceMessageProcessor::class);
        $this->app->singleton(CommerceMessageProcessorInterface::class, CommerceMessageProcessor::class);
    }

    private function offDriver($app): bool
    {
        return $app['config']->get('messaging.driver', 'live') === 'off'
            || $app->runningUnitTests();
    }
}
