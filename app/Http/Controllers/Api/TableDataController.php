<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Services\ConnectionManager;
use App\Services\TableDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TableDataController extends Controller
{
    public function __construct(
        private ConnectionManager $connectionManager,
        private TableDataService $tableDataService
    ) {
    }

    public function index(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('view', $connection);

        $limit = (int) $request->query('limit', 50);
        $offset = (int) $request->query('offset', 0);
        $schema = $request->query('schema');
        $database = $request->query('database');

        $config = $this->connectionManager->resolveConfig($connection->id);
        
        if ($database) {
            $config = $config->with(['database' => $database]);
        }

        $result = $this->tableDataService->getPaginatedData($config, $table, $limit, $offset, [
            'schema' => $schema
        ]);

        return response()->json($result);
    }

    public function store(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'data' => 'required|array',
            'schema' => 'nullable|string',
            'database' => 'nullable|string',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        
        if ($request->has('database')) {
            $config = $config->with(['database' => $request->input('database')]);
        }

        $success = $this->tableDataService->insertRecord(
            $config, 
            $table, 
            $request->input('data'),
            ['schema' => $request->input('schema')]
        );

        if (!$success) {
            return response()->json(['message' => 'Failed to insert record'], 500);
        }

        return response()->json(['message' => 'Record inserted successfully'], 201);
    }

    public function update(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'pk' => 'required|array',
            'data' => 'required|array',
            'schema' => 'nullable|string',
            'database' => 'nullable|string',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        
        if ($request->has('database')) {
            $config = $config->with(['database' => $request->input('database')]);
        }

        $success = $this->tableDataService->updateRecord(
            $config, 
            $table, 
            $request->input('pk'),
            $request->input('data'),
            ['schema' => $request->input('schema')]
        );

        return response()->json(['message' => 'Record updated successfully']);
    }

    public function destroy(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'schema' => 'nullable|string',
            'database' => 'nullable|string',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        
        if ($request->has('database')) {
            $config = $config->with(['database' => $request->input('database')]);
        }

        // The PK should be sent as query parameters or in the request body
        $pk = $request->except(['schema', 'database']);

        if (empty($pk)) {
            return response()->json(['message' => 'Primary key columns required'], 422);
        }

        $this->tableDataService->deleteRecord(
            $config, 
            $table, 
            $pk,
            ['schema' => $request->input('schema')]
        );

        return response()->json(['message' => 'Record deleted successfully']);
    }
}
