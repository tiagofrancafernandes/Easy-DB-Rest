<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\SnippetType;
use App\Http\Controllers\Controller;
use App\Http\Resources\SnippetResource;
use App\Models\Snippet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SnippetController extends Controller
{
    use AuthorizesRequests;

    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();
        $snippets = Snippet::query()
            ->where('user_id', $user->id)
            ->orWhereHas('teams', function ($query) use ($user) {
                $query->whereHas('members', fn ($q) => $q->where('users.id', $user->id));
            })
            ->with(['user', 'tags'])
            ->get();

        return SnippetResource::collection($snippets);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'public_content_slug' => ['nullable', 'string', 'min:5', 'max:60'],
            'public_content_password' => ['nullable', 'string'],
            'public_content_index' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
        ]);

        $data['user_id'] = Auth::id();

        if (empty($data['type'])) {
            $data['type'] = SnippetType::tryFromMany($data['name'], SnippetType::TEXT);
        } else {
            $data['type'] = SnippetType::tryFromMany($data['type'], SnippetType::TEXT);
        }

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $snippet = Snippet::create($data);

        if (!empty($tags)) {
            $snippet->syncTags($tags);
        }

        return (new SnippetResource($snippet->load(['user', 'tags'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Snippet $snippet): SnippetResource
    {
        $this->authorize('view', $snippet);

        return new SnippetResource($snippet->load(['user', 'tags']));
    }

    public function update(Request $request, Snippet $snippet): SnippetResource
    {
        $this->authorize('update', $snippet);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'public_content_slug' => ['nullable', 'string', 'min:5', 'max:60'],
            'public_content_password' => ['nullable', 'string'],
            'public_content_index' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
        ]);

        if (isset($data['type'])) {
            $data['type'] = SnippetType::tryFromMany($data['type'], SnippetType::TEXT);
        }

        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        $snippet->update($data);

        if ($tags !== null) {
            $snippet->syncTags($tags);
        }

        return new SnippetResource($snippet->fresh(['user', 'tags']));
    }

    public function destroy(Snippet $snippet): JsonResponse
    {
        $this->authorize('delete', $snippet);

        $snippet->delete();

        return response()->json(null, 204);
    }

    public function share(Request $request, Snippet $snippet): JsonResponse
    {
        $this->authorize('update', $snippet);

        $data = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'permission' => ['required', Rule::in(['view', 'edit', 'full'])],
        ]);

        $snippet->teams()->syncWithoutDetaching([
            $data['team_id'] => ['permission' => $data['permission']]
        ]);

        return response()->json(['message' => 'Snippet shared successfully']);
    }

    public function publicView(Request $request, string $userId, string $slug)
    {
        $snippet = Snippet::where('user_id', $userId)
            ->where('public_content_slug', $slug)
            ->firstOrFail();

        $providedPassword = $request->header('X-Content-Pass') ?? $request->query('password');

        if ($snippet->public_content_password) {
            if (!$providedPassword || $providedPassword !== $snippet->public_content_password) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Password required'], 401);
            }
        }

        $robots = $snippet->public_content_index ? 'index, follow' : 'noindex, nofollow';

        if ($request->has('text')) {
            return response($snippet->content)
                ->header('Content-Type', 'text/plain')
                ->header('X-Robots-Tag', $robots);
        }

        return (new SnippetResource($snippet->load(['user', 'tags'])))
            ->additional(['meta' => ['robots' => $robots]])
            ->response()
            ->header('X-Robots-Tag', $robots);
    }
}
