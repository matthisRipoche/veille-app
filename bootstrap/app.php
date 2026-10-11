<?php

use App\Http\Middleware\ApiToken;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SitePassword;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/healthz',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['veille.token' => ApiToken::class]);

        // Derrière un reverse proxy HTTPS : fait confiance à X-Forwarded-* pour générer des liens https.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        // Lecture du site : mot de passe optionnel (/healthz et l'API restent hors du groupe web).
        $middleware->web(append: [SitePassword::class]);
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'mcp/*') || $request->expectsJson(),
        );
    })->create();
