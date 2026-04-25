<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();
        $teams = Team::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->with('owner')
            ->get();

        return TeamResource::collection($teams);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $data['owner_id'] = Auth::id();

        $team = Team::create($data);
        $team->members()->attach(Auth::id());

        return (new TeamResource($team->load('owner')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Team $team): TeamResource
    {
        $this->authorize('view', $team);

        return new TeamResource($team->load(['owner', 'members']));
    }

    public function update(Request $request, Team $team): TeamResource
    {
        $this->authorize('update', $team);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team->update($data);

        return new TeamResource($team->fresh('owner'));
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return response()->json(null, 204);
    }

    public function addMember(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $team->members()->syncWithoutDetaching([$data['user_id']]);

        return response()->json(['message' => 'Member added successfully']);
    }

    public function removeMember(Team $team, User $user): JsonResponse
    {
        $this->authorize('update', $team);

        if ($user->id === $team->owner_id) {
            return response()->json(['error' => 'Cannot remove owner from team'], 422);
        }

        $team->members()->detach($user->id);

        return response()->json(['message' => 'Member removed successfully']);
    }
}
