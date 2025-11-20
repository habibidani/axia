<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Re-enable proxy trust for production
        $middleware->trustProxies(at: ['*'], headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);
        
        // Re-enable CSRF protection (exclude API and public webhooks)
        $middleware->validateCsrfTokens(except: [
            'api/*',  // API routes use Sanctum
            'webhooks/n8n/*',  // Public webhooks use signature verification
        ]);

        // Sanctum stateful domains for SPA authentication
        $middleware->statefulApi();
        
        // Register custom middleware aliases
        $middleware->alias([
            'verify.mcp.secret' => \App\Http\Middleware\VerifyMcpSecret::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
