<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\AdminCreateUserDTO;
use App\DTOs\UpdateUserPermissionsDTO;
use App\DTOs\UpdateUserRoleDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\UpdateUserPermissionsRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminUserController extends Controller
{
    public function __construct(
        protected UserManagementService $userManagementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $filters = [
            'search' => $request->query('search'),
            'role' => $request->query('role'),
            'per_page' => $request->query('per_page', 15),
        ];

        $users = $this->userManagementService->listUsers($filters);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(AdminCreateUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $dto = AdminCreateUserDTO::fromArray($request->validated());
        $user = $this->userManagementService->createUser($dto);

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'message' => 'Usuário criado com sucesso.',
            ],
        ], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $this->userManagementService->deleteUser($user);

        return response()->json(null, 204);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        Gate::authorize('updateRole', $user);

        /** @var User $actor */
        $actor = $request->user();
        $dto = UpdateUserRoleDTO::fromArray($request->validated());
        $updatedUser = $this->userManagementService->updateUserRole($actor, $user, $dto);

        return response()->json([
            'data' => new UserResource($updatedUser),
            'meta' => [
                'message' => 'Papel do usuário atualizado com sucesso.',
            ],
        ]);
    }

    public function updatePermissions(UpdateUserPermissionsRequest $request, User $user): JsonResponse
    {
        Gate::authorize('updatePermissions', $user);

        /** @var User $actor */
        $actor = $request->user();
        $dto = UpdateUserPermissionsDTO::fromArray($request->validated());
        $updatedUser = $this->userManagementService->updateUserPermissions($actor, $user, $dto);

        return response()->json([
            'data' => new UserResource($updatedUser),
            'meta' => [
                'message' => 'Permissões do usuário atualizadas com sucesso.',
            ],
        ]);
    }
}
