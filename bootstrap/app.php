<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\CspHeadersMiddleware::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\SecureSessionMiddleware::class,
            \App\Http\Middleware\CsrfProtectionMiddleware::class,
        ]);
        
        $middleware->alias([
            'pin.required' => \App\Http\Middleware\PinRequiredMiddleware::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'order.validate' => \App\Http\Middleware\OrderValidationMiddleware::class,
            'admin.protect' => \App\Http\Middleware\AdminProtectionMiddleware::class,
            'user.protect' => \App\Http\Middleware\UserProtectionMiddleware::class,
            'vendor.protect' => \App\Http\Middleware\VendorProtectionMiddleware::class,
            'wallet.balance' => \App\Http\Middleware\WalletBalanceMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

