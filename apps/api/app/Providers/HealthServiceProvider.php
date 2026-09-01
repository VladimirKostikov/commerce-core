<?php

namespace App\Providers;

use App\Contracts\HealthCheckerInterface;
use App\Contracts\HealthServiceInterface;
use App\Contracts\TcpConnectorInterface;
use App\Services\Health\Checkers\ClickHouseHealthChecker;
use App\Services\Health\Checkers\KafkaHealthChecker;
use App\Services\Health\Checkers\PostgresHealthChecker;
use App\Services\Health\Checkers\RabbitMqHealthChecker;
use App\Services\Health\Checkers\RedisHealthChecker;
use App\Services\Health\HealthService;
use App\Services\Network\FsockopenTcpConnector;
use Illuminate\Support\ServiceProvider;

final class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TcpConnectorInterface::class, FsockopenTcpConnector::class);

        $this->app->tag([
            PostgresHealthChecker::class,
            RedisHealthChecker::class,
            RabbitMqHealthChecker::class,
            KafkaHealthChecker::class,
            ClickHouseHealthChecker::class,
        ], HealthCheckerInterface::class);

        $this->app->singleton(HealthServiceInterface::class, function ($app): HealthService {
            return new HealthService($app->tagged(HealthCheckerInterface::class));
        });
    }
}
