<?php

use App\Providers\AppServiceProvider;
use App\Providers\CommerceServiceProvider;
use App\Providers\HealthServiceProvider;
use App\Providers\MessagingServiceProvider;
use App\Providers\TestingServiceProvider;

return [
    AppServiceProvider::class,
    HealthServiceProvider::class,
    MessagingServiceProvider::class,
    CommerceServiceProvider::class,
    TestingServiceProvider::class,
];
