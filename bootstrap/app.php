<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\JWTAuthMiddleware;
use App\Http\Middleware\JWTRefreshMiddleware;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register JWT middleware aliases
        $middleware->alias([
            'jwt.auth' => JWTAuthMiddleware::class,
            'jwt.refresh' => JWTRefreshMiddleware::class,
        ]);

        // Configure session middleware for web routes
        $middleware->web([
            \App\Http\Middleware\EncryptCookies::class, // Custom EncryptCookies that excludes jwt_token
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API middleware group (stateless, no sessions)
        $middleware->api([
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Note: We don't set redirectGuestsTo here because our JWT middleware
        // handles its own redirects. Setting it globally can cause redirect loops.
    })
    ->booted(function ($app) {
        // Re-register JWT middleware aliases AFTER package service providers boot
        // The jwt-auth package overwrites these in its service provider boot method
        $app['router']->aliasMiddleware('jwt.auth', JWTAuthMiddleware::class);
        $app['router']->aliasMiddleware('jwt.refresh', JWTRefreshMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle authentication exceptions
        $exceptions->render(function (AuthenticationException $e, $request) {
            // Don't redirect if already on login page
            if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                return null;
            }

            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to access this resource.',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            // For web requests, redirect to login page
            return redirect()->guest(route('admin.login'));
        });

        // Handle JWT Token Expired
        $exceptions->render(function (TokenExpiredException $e, $request) {
            // Don't redirect if already on login page
            if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. Please refresh or login again.',
                    'error_code' => 'TOKEN_EXPIRED',
                ], 401);
            }

            return redirect()->guest(route('admin.login'))
                ->with('error', 'Your session has expired. Please login again.');
        });

        // Handle JWT Token Invalid
        $exceptions->render(function (TokenInvalidException $e, $request) {
            // Don't redirect if already on login page
            if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid. Please login again.',
                    'error_code' => 'TOKEN_INVALID',
                ], 401);
            }

            return redirect()->guest(route('admin.login'))
                ->with('error', 'Invalid authentication. Please login again.');
        });

        // Handle General JWT Exceptions
        $exceptions->render(function (JWTException $e, $request) {
            // Don't redirect if already on login page
            if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication error: ' . $e->getMessage(),
                    'error_code' => 'JWT_ERROR',
                ], 401);
            }

            return redirect()->guest(route('admin.login'))
                ->with('error', 'Authentication error. Please login again.');
        });
    })->create();
