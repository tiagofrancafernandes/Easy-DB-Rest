<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\QueryPayloadDto;
use App\Exceptions\ConnectionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Http\Resources\QueryResultResource;
use App\Services\QueryExecutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    protected const SQL_CONTENT_TYPES = [
        'application/sql',
        'text/x-sql',
        'text/x-mysql',
        'text/x-pgsql',
        'text/x-plsql',
        'text/x-mssql',
    ];

    public function __construct(
        protected readonly QueryExecutorService $executor,
    ) {
    }

    public function query(Request $request): JsonResponse
    {
        $contentType = strtolower((string) $request->header('Content-Type', ''));

        if ($this->isSqlContentType($contentType)) {
            return $this->executeRawFromBody($request);
        }

        return $this->executeFromJson(app(QueryRequest::class));
    }

    public function raw(Request $request): JsonResponse
    {
        return $this->executeRawFromBody($request);
    }

    public function builder(QueryRequest $request): JsonResponse
    {
        try {
            $payload = QueryPayloadDto::fromArray($request->validated());

            $result = $this->executor->execute(
                payload:           $payload,
                configId:          $request->header('X-Config-ID') ?? $request->input('config_id'),
                inlineConnection:  $request->input('connection'),
                overrides:         $request->input('overrides', []),
            );

            return (new QueryResultResource($result))->response();
        } catch (\Throwable $e) {
            if (!app()->environment('production') && config('app.debug')) {
                throw $e;
            }

            return response()->json([
                'error'   => true,
                'message' => 'Query builder execution failed: ' . $e->getMessage(),
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    public function test(Request $request): JsonResponse
    {
        try {
            $configId    = $request->header('X-Config-ID') ?? $request->input('config_id');
            $inline      = $request->input('connection');
            $overrides   = $request->input('overrides', []);

            $this->guardConnectionInput($configId, $inline);

            $this->executor->testConnection($configId, $inline, $overrides);

            return response()->json([
                'connected' => true,
                'message'   => 'Connection established successfully.',
            ]);
        } catch (\Throwable $e) {
            if (!app()->environment('production') && config('app.debug')) {
                throw $e;
            }

            return response()->json([
                'connected' => false,
                'message'   => 'Connection test failed: ' . $e->getMessage(),
                'error'     => $e->getMessage(),
            ], 422);
        }
    }

    protected function executeRawFromBody(Request $request): JsonResponse
    {
        try {
            $sql = trim($request->getContent());

            if ($sql === '') {
                return response()->json([
                    'error'   => true,
                    'message' => 'Request body must contain a SQL query.',
                    'code'    => 'EMPTY_QUERY',
                    'details' => (object) [],
                ], 422);
            }

            $configId  = $request->header('X-Config-ID') ?? $request->input('config_id');
            $inline    = $request->input('connection');
            $overrides = $request->input('overrides', []);

            $this->guardConnectionInput($configId, $inline);

            $payload = QueryPayloadDto::fromRawSql($sql);

            $result = $this->executor->execute(
                payload:          $payload,
                configId:         $configId,
                inlineConnection: $inline,
                overrides:        $overrides,
            );

            return (new QueryResultResource($result))->response();
        } catch (\Throwable $e) {
            if (!app()->environment('production') && config('app.debug')) {
                throw $e;
            }

            return response()->json([
                'error'   => true,
                'message' => 'Raw query execution failed: ' . $e->getMessage(),
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    protected function executeFromJson(QueryRequest $request): JsonResponse
    {
        try {
            $payload = QueryPayloadDto::fromArray($request->validated());

            $result = $this->executor->execute(
                payload:          $payload,
                configId:         $request->header('X-Config-ID') ?? $request->input('config_id'),
                inlineConnection: $request->input('connection'),
                overrides:        $request->input('overrides', []),
            );

            return (new QueryResultResource($result))->response();
        } catch (\Throwable $e) {
            if (!app()->environment('production') && config('app.debug')) {
                throw $e;
            }

            return response()->json([
                'error'   => true,
                'message' => 'Query execution failed: ' . $e->getMessage(),
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    protected function isSqlContentType(string $contentType): bool
    {
        foreach (static::SQL_CONTENT_TYPES as $type) {
            if (str_starts_with($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    protected function guardConnectionInput(?string $configId, mixed $inline): void
    {
        if ($configId !== null || $inline !== null) {
            return;
        }

        throw ConnectionException::connectionFailed('Provide X-Config-ID header or a connection object in the request body.');
    }
}
