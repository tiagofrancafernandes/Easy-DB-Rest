<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConnectionRequest;
use App\Http\Requests\UpdateConnectionRequest;
use App\Http\Resources\ConnectionResource;
use App\Models\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConnectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ConnectionResource::collection(Connection::all());
    }

    public function store(StoreConnectionRequest $request): JsonResponse
    {
        $connection = Connection::create($request->validated());

        return (new ConnectionResource($connection))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): ConnectionResource
    {
        $connection = Connection::findOrFail($id);

        return new ConnectionResource($connection);
    }

    public function update(UpdateConnectionRequest $request, string $id): ConnectionResource
    {
        $connection = Connection::findOrFail($id);

        $connection->update($request->validated());

        return new ConnectionResource($connection->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $connection = Connection::findOrFail($id);

        $connection->delete();

        return response()->json(null, 204);
    }
}
