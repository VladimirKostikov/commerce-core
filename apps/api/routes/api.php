<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\StubSupplierController;
use App\Http\Controllers\TestRunController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::controller(CatalogController::class)->prefix('catalog')->group(function (): void {
    Route::get('/', 'index');
    Route::get('/{sku}', 'show');
});

Route::controller(AuthController::class)->group(function (): void {
    Route::post('/register', 'register');
    Route::post('/login', 'login')->name('login');
});

Route::prefix('webhook')->group(function (): void {
    Route::post('/payment', PaymentWebhookController::class);
});

Route::get('/reconciliation', ReconciliationController::class);

Route::controller(TestRunController::class)->prefix('tests')->group(function (): void {
    Route::match(['get', 'post'], '/run', 'run');
    Route::get('/log', 'log');
});

Route::prefix('stub/suppliers')->group(function (): void {
    Route::post('/{supplier}/issue', StubSupplierController::class);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::controller(AuthController::class)->group(function (): void {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });

    Route::controller(OrderController::class)->prefix('orders')->group(function (): void {
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
    });
});
