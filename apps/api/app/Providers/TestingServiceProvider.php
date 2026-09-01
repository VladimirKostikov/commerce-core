<?php

namespace App\Providers;

use App\Contracts\TestCatalogInterface;
use App\Contracts\TestCatalogSyncInterface;
use App\Contracts\TestRunLogStoreInterface;
use App\Contracts\TestSuiteRunnerInterface;
use App\Services\Testing\ArtisanTestSuiteRunner;
use App\Services\Testing\PhpUnitTestCatalog;
use App\Services\Testing\RedisTestRunLogStore;
use App\Services\Testing\TestCatalogSync;
use Illuminate\Support\ServiceProvider;

final class TestingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TestCatalogInterface::class, function ($app): TestCatalogInterface {
            return new PhpUnitTestCatalog($app->basePath('tests'));
        });
        $this->app->singleton(TestRunLogStoreInterface::class, RedisTestRunLogStore::class);
        $this->app->singleton(TestSuiteRunnerInterface::class, ArtisanTestSuiteRunner::class);
        $this->app->singleton(TestCatalogSync::class);
        $this->app->singleton(TestCatalogSyncInterface::class, TestCatalogSync::class);
    }
}
