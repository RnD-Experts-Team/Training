<?php

use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'super_admin' => EnsureUserIsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Render a friendly full-page screen for common HTTP errors on web
        // (Inertia) requests, keeping the debug page for local 500s and JSON
        // responses for API clients.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request): Response {
            $status = $response->getStatusCode();
            $handled = [403, 404, 413, 419, 429, 500, 503];

            if ($request->is('api/*') || $request->expectsJson() || ! in_array($status, $handled, true)) {
                return $response;
            }

            if ($status === 500 && app()->hasDebugModeEnabled()) {
                return $response;
            }

            return Inertia::render('errors/error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
