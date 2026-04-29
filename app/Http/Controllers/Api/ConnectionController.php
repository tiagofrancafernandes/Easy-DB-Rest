<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConnectionRequest;
use App\Http\Requests\UpdateConnectionRequest;
use App\Http\Resources\ConnectionResource;
use App\Models\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:300'],
            'page' => ['nullable', 'integer', 'min:1'],
            'page_name' => ['nullable', 'string'],
        ]);

        $limit = $request->integer('limit', 40);
        $columns = (array) value(function () use ($request) {
            $v = $request->input('columns', '*');

            return array_filter(
                array_map(fn ($i) => is_string($i) ? trim($i) : '', $v = is_array($v) ? $v : [$v])
            );
        });

        $user = Auth::user();
        $connections = Connection::query()
            ->where('user_id', $user->id)
            ->orWhereHas('teams', function ($query) use ($user) {
                $query->whereHas('members', fn ($q) => $q->where('users.id', $user->id));
            })
            ->with(['user', 'tags'])
            ->paginate(
                perPage: $limit,
                columns: $columns ?: ['*'],
                pageName: $request->integer('page_name', 'page'),
                page: $request->integer('page'),
            );

        return ConnectionResource::collection($connections);
    }

    public function store(StoreConnectionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $connection = Connection::create($data);

        if (!empty($tags)) {
            $connection->syncTags($tags);
        }

        return (new ConnectionResource($connection->load(['user', 'tags'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): ConnectionResource
    {
        $connection = Connection::findOrFail($id);
        $this->authorize('view', $connection);

        return new ConnectionResource($connection->load(['user', 'tags']));
    }

    public function update(UpdateConnectionRequest $request, string $id): ConnectionResource
    {
        $connection = Connection::findOrFail($id);
        $this->authorize('update', $connection);

        $data = $request->validated();
        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        $connection->fill($data);

        if ($connection->isDirty()) {
            $connection->save();
        }

        if ($tags !== null) {
            $connection->syncTags($tags);
        }

        return new ConnectionResource($connection->fresh(['user', 'tags']));
    }

    public function destroy(string $id): JsonResponse
    {
        $connection = Connection::findOrFail($id);
        $this->authorize('delete', $connection);

        $connection->delete();

        return response()->json(null, 204);
    }

    public function share(Request $request, string $id): JsonResponse
    {
        $connection = Connection::findOrFail($id);
        $this->authorize('update', $connection);

        $data = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'permission' => ['required', \Illuminate\Validation\Rule::in(['view', 'edit', 'full'])],
        ]);

        $connection->teams()->syncWithoutDetaching([
            $data['team_id'] => ['permission' => $data['permission']]
        ]);

        return response()->json(['message' => 'Connection shared successfully']);
    }
}
