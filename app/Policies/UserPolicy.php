<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.invite') || $user->hasRole('ADMIN');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can('user.invite') || $user->hasRole('ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->can('user.invite') || $user->hasRole('ADMIN');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can('user.promote') || $user->hasRole('ADMIN');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('user.remove') || $user->hasRole('ADMIN');
    }

    public function updateRole(User $user, User $model): bool
    {
        return $user->can('user.promote') || $user->hasRole('ADMIN');
    }

    public function updatePermissions(User $user, User $model): bool
    {
        return $user->can('user.promote') || $user->hasRole('ADMIN');
    }
}
