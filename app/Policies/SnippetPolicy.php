<?php

namespace App\Policies;

use App\Models\Snippet;
use App\Models\User;

class SnippetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Snippet $snippet): bool
    {
        return $user->id === $snippet->user_id ||
            $snippet->teams()->whereHas('members', fn ($q) => $q->where('users.id', $user->id))->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Snippet $snippet): bool
    {
        if ($user->id === $snippet->user_id) {
            return true;
        }

        return $snippet->teams()
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->wherePivotIn('permission', ['edit', 'full'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Snippet $snippet): bool
    {
        if ($user->id === $snippet->user_id) {
            return true;
        }

        return $snippet->teams()
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->wherePivot('permission', 'full')
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Snippet $snippet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Snippet $snippet): bool
    {
        return false;
    }
}
