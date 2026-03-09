<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission'  => \App\Http\Middleware\CheckPermission::class,
            'force.json'  => \App\Http\Middleware\ForceJsonResponse::class,
            'auth.proxy'  => \App\Http\Middleware\AuthProxyMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->shouldRenderJsonWhen(function ($request) {
            return true;
        });

        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unauthenticated',
            ], 401);
        });

        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            $statusCode = $e->getStatusCode();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: match ($statusCode) {
                    401 => 'Unauthorized',
                    403 => 'Forbidden',
                    404 => 'Not found',
                    500 => 'Server error',
                    default => 'An error occurred',
                },
            ], $statusCode);
        });

        $exceptions->render(function (Throwable $e, $request) {
            logger()->error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        });
    })

    ->create();
