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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions with custom formatting
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                    'status' => 422,
                    'timestamp' => now()->toISOString(),
                ], 422);
            }
        });

        // Handle authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 401,
                    'timestamp' => now()->toISOString(),
                ], 401);
            }
        });

        // Handle authorization exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This action is unauthorized.',
                    'status' => 403,
                    'timestamp' => now()->toISOString(),
                ], 403);
            }
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                $model = class_basename($e->getModel());
                return response()->json([
                    'message' => "{$model} not found.",
                    'status' => 404,
                    'timestamp' => now()->toISOString(),
                ], 404);
            }
        });

        // Handle method not allowed exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Method not allowed.',
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? null,
                    'status' => 405,
                    'timestamp' => now()->toISOString(),
                ], 405);
            }
        });

        // Handle general HTTP exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'HTTP Error',
                    'status' => $e->getStatusCode(),
                    'timestamp' => now()->toISOString(),
                ], $e->getStatusCode());
            }
        });

        // Handle file upload exceptions
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'File upload too large.',
                    'status' => 413,
                    'timestamp' => now()->toISOString(),
                ], 413);
            }
        });
    })->create();
