<?php

namespace Tests\Component;

use App\Contracts\CatalogStorefrontInterface;
use App\Contracts\CommerceEventBusInterface;
use App\Contracts\CommerceWorkQueueInterface;
use App\Services\Catalog\CachedCatalogStorefront;
use App\Services\Messaging\NullCommerceEventBus;
use App\Services\Messaging\NullCommerceWorkQueue;
use Tests\TestCase;

final class MessagingBindingsTest extends TestCase
{
    public function test_catalog_storefront_is_cached_decorator(): void
    {
        $this->assertInstanceOf(
            CachedCatalogStorefront::class,
            $this->app->make(CatalogStorefrontInterface::class),
        );
    }

    public function test_testing_environment_binds_null_brokers(): void
    {
        $this->assertInstanceOf(
            NullCommerceEventBus::class,
            $this->app->make(CommerceEventBusInterface::class),
        );
        $this->assertInstanceOf(
            NullCommerceWorkQueue::class,
            $this->app->make(CommerceWorkQueueInterface::class),
        );
    }
}
