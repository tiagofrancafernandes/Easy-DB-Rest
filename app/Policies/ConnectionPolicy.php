<?php

namespace App\Policies;

use App\Models\Connection;
use App\Models\User;

class ConnectionPolicy
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
    public function view(User $user, Connection $connection): bool
    {
        return $user->id === $connection->user_id ||
            $connection->teams()->whereHas('members', fn ($q) => $q->where('users.id', $user->id))->exists();
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
    public function update(User $user, Connection $connection): bool
    {
        if ($user->id === $connection->user_id) {
            return true;
        }

        return $connection->teams()
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->wherePivotIn('permission', ['edit', 'full'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Connection $connection): bool
    {
        if ($user->id === $connection->user_id) {
            return true;
        }

        return $connection->teams()
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->wherePivot('permission', 'full')
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Connection $connection): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Connection $connection): bool
    {
        return false;
    }
}
