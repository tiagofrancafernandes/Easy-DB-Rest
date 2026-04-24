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
    private const SQL_CONTENT_TYPES = [
        'application/sql',
        'text/x-sql',
        'text/x-mysql',
        'text/x-pgsql',
        'text/x-plsql',
        'text/x-mssql',
    ];

    public function __construct(
        private readonly QueryExecutorService $executor,
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
        $payload = QueryPayloadDto::fromArray($request->validated());

        $result = $this->executor->execute(
            payload:           $payload,
            configId:          $request->header('X-Config-ID') ?? $request->input('config_id'),
            inlineConnection:  $request->input('connection'),
            overrides:         $request->input('overrides', []),
        );

        return (new QueryResultResource($result))->response();
    }

    public function test(Request $request): JsonResponse
    {
        $configId    = $request->header('X-Config-ID') ?? $request->input('config_id');
        $inline      = $request->input('connection');
        $overrides   = $request->input('overrides', []);

        $this->guardConnectionInput($configId, $inline);

        $this->executor->testConnection($configId, $inline, $overrides);

        return response()->json([
            'connected' => true,
            'message'   => 'Connection established successfully.',
        ]);
    }

    private function executeRawFromBody(Request $request): JsonResponse
    {
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
    }

    private function executeFromJson(QueryRequest $request): JsonResponse
    {
        $payload = QueryPayloadDto::fromArray($request->validated());

        $result = $this->executor->execute(
            payload:          $payload,
            configId:         $request->header('X-Config-ID') ?? $request->input('config_id'),
            inlineConnection: $request->input('connection'),
            overrides:        $request->input('overrides', []),
        );

        return (new QueryResultResource($result))->response();
    }

    private function isSqlContentType(string $contentType): bool
    {
        foreach (self::SQL_CONTENT_TYPES as $type) {
            if (str_starts_with($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    private function guardConnectionInput(?string $configId, mixed $inline): void
    {
        if ($configId !== null || $inline !== null) {
            return;
        }

        throw ConnectionException::connectionFailed('Provide X-Config-ID header or a connection object in the request body.');
    }
}
