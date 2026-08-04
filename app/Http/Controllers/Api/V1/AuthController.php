<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\LoginDTO;
use App\DTOs\RegisterUserDTO;
use App\DTOs\ResetPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $dto = RegisterUserDTO::fromArray($request->validated());
        $result = $this->authService->register($dto);

        return response()->json([
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'meta' => [
                'message' => 'Usuário registrado com sucesso.',
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromArray($request->validated());
        $result = $this->authService->login($dto);

        return response()->json([
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'meta' => [
                'message' => 'Login realizado com sucesso.',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->authService->logout($user);

        return response()->json(null, 204);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [],
        ]);
    }

    public function googleRedirect(): JsonResponse
    {
        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');
        $url = $driver->stateless()->redirect()->getTargetUrl();

        return response()->json([
            'data' => [
                'url' => $url,
            ],
            'meta' => [],
        ]);
    }

    public function googleCallback(Request $request): JsonResponse
    {
        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');
        $socialUser = $driver->stateless()->user();
        $result = $this->authService->handleGoogleCallback($socialUser);

        return response()->json([
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'meta' => [
                'message' => 'Login via Google realizado com sucesso.',
            ],
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetLink($request->validated('email'));

        return response()->json([
            'data' => [],
            'meta' => [
                'message' => 'Se o e-mail existir em nossa base, o link de recuperação de senha foi enviado.',
            ],
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $dto = ResetPasswordDTO::fromArray($request->validated());
        $this->authService->resetPassword($dto);

        return response()->json([
            'data' => [],
            'meta' => [
                'message' => 'Senha redefinida com sucesso.',
            ],
        ]);
    }
}
