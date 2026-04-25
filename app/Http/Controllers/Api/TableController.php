<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Services\ConnectionManager;
use App\Services\SchemaManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TableController extends Controller
{
    public function __construct(
        private ConnectionManager $connectionManager,
        private SchemaManagerService $schemaManager
    ) {
    }

    public function index(Request $request, Connection $connection): JsonResponse
    {
        Gate::authorize('view', $connection);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $tables = $this->schemaManager->listTables($config);

        return response()->json(['data' => $tables]);
    }

    public function show(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('view', $connection);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $details = $this->schemaManager->getTableDetails($config, $table);

        return response()->json(['data' => $details]);
    }

    public function store(Request $request, Connection $connection): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'table' => 'required|string',
            'columns' => 'required|array',
            'columns.*.name' => 'required|string',
            'columns.*.type' => 'required|string',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->createTable(
            $config,
            $request->string('table')->toString(),
            $request->input('columns')
        );

        return response()->json(['message' => 'Table created successfully'], 201);
    }

    public function update(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'add' => 'array',
            'drop' => 'array',
            'modify' => 'array',
            'rename' => 'array',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->alterTable($config, $table, $request->all());

        return response()->json(['message' => 'Table altered successfully']);
    }

    public function destroy(Request $request, Connection $connection, string $table): JsonResponse
    {
        Gate::authorize('update', $connection);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->dropTable($config, $table);

        return response()->json(['message' => 'Table dropped successfully']);
    }
}
