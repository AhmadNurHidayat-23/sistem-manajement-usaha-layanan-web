<?php

use App\Http\Middleware\MobileAuthenticate;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'mobile.auth' => MobileAuthenticate::class,
        ]);

        // Exclude the payment gateway webhook from CSRF — gateway POSTs without a session token
        $middleware->validateCsrfTokens(except: [
            'payment/webhook',
        ]);

        // Biarkan cookie preferensi UI sidebar dibaca langsung tanpa enkripsi session
        $middleware->encryptCookies(except: [
            'sidebar_collapsed',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
