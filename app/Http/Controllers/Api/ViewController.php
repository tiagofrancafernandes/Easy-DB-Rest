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

class ViewController extends Controller
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
        $views = $this->schemaManager->listViews($config);

        return response()->json(['data' => $views]);
    }

    public function store(Request $request, Connection $connection): JsonResponse
    {
        Gate::authorize('update', $connection);

        $request->validate([
            'name' => 'required|string',
            'query' => 'required|string',
        ]);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->createView(
            $config,
            $request->string('name')->toString(),
            $request->string('query')->toString()
        );

        return response()->json(['message' => 'View created successfully'], 201);
    }

    public function destroy(Request $request, Connection $connection, string $name): JsonResponse
    {
        Gate::authorize('update', $connection);

        $config = $this->connectionManager->resolveConfig($connection->id);
        $this->schemaManager->dropView($config, $name);

        return response()->json(['message' => 'View dropped successfully']);
    }
}
