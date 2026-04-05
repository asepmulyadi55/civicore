<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission'  => \App\Http\Middleware\RequirePermission::class,
            'single.session' => \App\Http\Middleware\CheckSingleSession::class,
            'api.key'     => \App\Http\Middleware\VerifyApiKey::class,
        ]);

        $middleware->prependToGroup('web', [
            \App\Http\Middleware\UpdateLastActive::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CheckSingleSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
