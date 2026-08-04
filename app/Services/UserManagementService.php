<?php

namespace App\Services;

use App\DTOs\AdminCreateUserDTO;
use App\DTOs\UpdateUserPermissionsDTO;
use App\DTOs\UpdateUserRoleDTO;
use App\Enums\UserRole;
use App\Models\PermissionAuditLog;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * @param  array{search?: string|null, role?: string|null, per_page?: int}  $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function listUsers(array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($filters);
    }

    public function createUser(AdminCreateUserDTO $dto): User
    {
        /** @var User $user */
        $user = $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        $role = $dto->role ?? UserRole::COMMON->value;
        $user->assignRole($role);

        return $user->fresh(['roles', 'permissions']);
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    public function updateUserRole(User $actor, User $target, UpdateUserRoleDTO $dto): User
    {
        $oldRoles = $target->getRoleNames()->toArray();

        $target->syncRoles([$dto->role]);

        $newRoles = [$dto->role];

        PermissionAuditLog::create([
            'actor_id' => $actor->id,
            'target_id' => $target->id,
            'action' => 'role_change',
            'old_values' => ['roles' => $oldRoles],
            'new_values' => ['roles' => $newRoles],
            'created_at' => now(),
        ]);

        return $target->fresh(['roles', 'permissions']);
    }

    public function updateUserPermissions(User $actor, User $target, UpdateUserPermissionsDTO $dto): User
    {
        $oldDirectPermissions = $target->getDirectPermissions()->pluck('name')->toArray();

        if ($dto->action === 'grant') {
            $target->givePermissionTo($dto->permissions);
        } elseif ($dto->action === 'revoke') {
            $target->revokePermissionTo($dto->permissions);
        } else {
            // sync
            $target->syncPermissions($dto->permissions);
        }

        $newDirectPermissions = $target->fresh()->getDirectPermissions()->pluck('name')->toArray();

        PermissionAuditLog::create([
            'actor_id' => $actor->id,
            'target_id' => $target->id,
            'action' => 'permission_'.$dto->action,
            'old_values' => ['direct_permissions' => $oldDirectPermissions],
            'new_values' => ['direct_permissions' => $newDirectPermissions],
            'created_at' => now(),
        ]);

        return $target->fresh(['roles', 'permissions']);
    }
}
