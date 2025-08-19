<?php

use App\Helpers\ResponseFormatter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseFormatter::error(null, 'Unauthenticated.', 401);
            }
        });

        // 403: sudah login tapi tak punya izin
        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseFormatter::error(null, 'Forbidden.', 403);
            }
        });

        // 422: validasi
        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseFormatter::error($e->errors(), 'The given data was invalid.', 422);
            }
        });

        // 404 & 405: rapi untuk API
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return ResponseFormatter::error(null, 'Not Found.', 404);
            }
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return ResponseFormatter::error(null, 'Method Not Allowed.', 405);
            }
        });

        // 429: rate limit
        $exceptions->renderable(function (ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                return ResponseFormatter::error(null, 'Too Many Requests.', 429);
            }
        });

        // 500 fallback: jangan bocorkan trace ke klien
        $exceptions->renderable(function (\Throwable $th, $request) {
            if ($request->is('api/*')) {
                return ResponseFormatter::error(null, 'Server Error.', 500);
            }
        });
    })->create();
