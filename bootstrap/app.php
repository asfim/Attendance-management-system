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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'log_activity' => \App\Http\Middleware\LogActivity::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'student/fees/pay/*/success',
            'student/fees/pay/*/fail',
            'student/fees/pay/*/cancel',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
