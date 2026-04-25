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

class DatabaseController extends Controller
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
        $databases = $this->schemaManager->listDatabases($config);

        return response()->json(['data' => $databases]);
    }

    public function store(Request $request, Connection $connection): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'name' => 'required|string',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->createDatabase($config, $request->string('name')->toString());

        return response()->json(['message' => 'Database created successfully'], 201);
    }

    public function destroy(Request $request, Connection $connection, string $name): JsonResponse
    {
        Gate::authorize('update', $connection);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->dropDatabase($config, $name);

        return response()->json(['message' => 'Database dropped successfully']);
    }
}
