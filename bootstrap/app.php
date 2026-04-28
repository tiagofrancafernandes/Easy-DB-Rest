<?php

use App\Exceptions\ConnectionException;
use App\Exceptions\QuerySecurityException;
use App\Exceptions\QueryTimeoutException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /** @suppress PHP0423 */
        $jsonError = static fn(string $message, string $code = 'UNKNOWN_ERROR', null|int $status = null, mixed $details = null) => new JsonResponse([
            'error'   => true,
            'message' => $message,
            'code'    => $code ?: 'UNKNOWN_ERROR',
            'details' => $details ?? (object) [],
        ], $status ?? 422);

        $exceptions->render(fn(ValidationException $e): JsonResponse => $jsonError($e->getMessage(), 'VALIDATION_ERROR', 422, $e->errors()));

        $exceptions->render(fn(QuerySecurityException $e): JsonResponse => $jsonError($e->getMessage(), 'QUERY_SECURITY_ERROR', $e->getCode() ?: 422));

        $exceptions->render(fn(QueryTimeoutException $e): JsonResponse => $jsonError($e->getMessage(), 'QUERY_TIMEOUT', $e->getCode() ?: 408, null));

        $exceptions->render(fn(ConnectionException $e): JsonResponse => $jsonError($e->getMessage(), 'CONNECTION_ERROR', $e->getCode() ?: 503));

        $exceptions->render(fn(HttpException $e): JsonResponse => $jsonError($e->getMessage() ?: 'HTTP error', 'HTTP_ERROR', $e->getStatusCode()));
    })->create();
