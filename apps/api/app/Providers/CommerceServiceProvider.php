<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\CatalogStorefrontInterface;
use App\Contracts\ClickHouseClientInterface;
use App\Contracts\CommerceEventBusInterface;
use App\Contracts\CommerceLoggerInterface;
use App\Contracts\DeliveryServiceInterface;
use App\Contracts\LedgerWriterInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\PaymentWebhookServiceInterface;
use App\Contracts\ProductKeyInventoryInterface;
use App\Contracts\ReconciliationServiceInterface;
use App\Contracts\StorefrontCacheInterface;
use App\Contracts\StuckOrderRecoveryInterface;
use App\Contracts\SupplierClientInterface;
use App\Contracts\SupplierInterface;
use App\Enums\SupplierName;
use App\Services\Auth\AuthService;
use App\Services\Catalog\CachedCatalogStorefront;
use App\Services\Catalog\CatalogStorefront;
use App\Services\Catalog\StorefrontCache;
use App\Services\ClickHouse\ClickHouseHttpClient;
use App\Services\Delivery\DeliveryGate;
use App\Services\Delivery\DeliveryService;
use App\Services\Inventory\EloquentProductKeyInventory;
use App\Services\Ledger\LedgerWriter;
use App\Services\Logging\ClickHouseCommerceLogger;
use App\Services\Logging\FanOutCommerceLogger;
use App\Services\Logging\NullCommerceLogger;
use App\Services\Orders\OrderService;
use App\Services\Orders\OrderStatusMachine;
use App\Services\Payments\PaymentWebhookService;
use App\Services\Reconciliation\ReconciliationService;
use App\Services\Recovery\StuckOrderFinder;
use App\Services\Recovery\StuckOrderRecovery;
use App\Services\Suppliers\DirectSupplierClient;
use App\Services\Suppliers\FallbackSupplier;
use App\Services\Suppliers\RemoteSupplier;
use App\Services\Suppliers\SupplierRetry;
use Illuminate\Support\ServiceProvider;

final class CommerceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CatalogStorefront::class);
        $this->app->singleton(StorefrontCacheInterface::class, StorefrontCache::class);
        $this->app->singleton(CatalogStorefrontInterface::class, CachedCatalogStorefront::class);
        $this->app->singleton(OrderStatusMachine::class);
        $this->app->singleton(DeliveryGate::class, function ($app): DeliveryGate {
            return new DeliveryGate(
                $app->make(OrderStatusMachine::class),
                (int) $app['config']->get('recovery.stale_after_seconds', 30),
            );
        });
        $this->app->singleton(ClickHouseClientInterface::class, ClickHouseHttpClient::class);
        $this->app->singleton(CommerceLoggerInterface::class, function ($app): CommerceLoggerInterface {
            if ($app['config']->get('messaging.driver', 'live') === 'off' || $app->runningUnitTests()) {
                return new NullCommerceLogger();
            }

            return new FanOutCommerceLogger(
                $app->make(ClickHouseCommerceLogger::class),
                $app->make(CommerceEventBusInterface::class),
            );
        });
        $this->app->singleton(LedgerWriterInterface::class, LedgerWriter::class);
        $this->app->singleton(ReconciliationServiceInterface::class, ReconciliationService::class);
        $this->app->singleton(StuckOrderFinder::class, function ($app): StuckOrderFinder {
            return new StuckOrderFinder(
                (int) $app['config']->get('recovery.stale_after_seconds', 30),
            );
        });
        $this->app->singleton(StuckOrderRecoveryInterface::class, StuckOrderRecovery::class);
        $this->app->singleton(ProductKeyInventoryInterface::class, EloquentProductKeyInventory::class);
        $this->app->singleton(SupplierClientInterface::class, DirectSupplierClient::class);
        $this->app->singleton(SupplierRetry::class, function ($app): SupplierRetry {
            return new SupplierRetry(
                (int) $app['config']->get('suppliers.retries', 3),
                (int) $app['config']->get('suppliers.backoff_ms', 0),
            );
        });
        $this->app->singleton(SupplierInterface::class, function ($app): FallbackSupplier {
            $client = $app->make(SupplierClientInterface::class);

            return new FallbackSupplier(
                $app->make(SupplierRetry::class),
                new RemoteSupplier(SupplierName::A, $client),
                new RemoteSupplier(SupplierName::B, $client),
            );
        });
        $this->app->singleton(DeliveryServiceInterface::class, DeliveryService::class);
        $this->app->singleton(PaymentWebhookServiceInterface::class, PaymentWebhookService::class);
        $this->app->singleton(OrderServiceInterface::class, OrderService::class);
        $this->app->singleton(AuthServiceInterface::class, AuthService::class);
    }
}
