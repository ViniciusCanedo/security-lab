<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\DTOs\RegisterUserDTO;
use App\DTOs\ResetPasswordDTO;
use App\Enums\UserRole;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidResetTokenException;
use App\Jobs\SendMagicLinkResetJob;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function register(RegisterUserDTO $dto): array
    {
        /** @var User $user */
        $user = $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        $user->assignRole(UserRole::COMMON->value);

        SendWelcomeEmailJob::dispatch($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->fresh(['roles', 'permissions']),
            'token' => $token,
        ];
    }

    /**
     * @return array{user: User, token: string}
     *
     * @throws InvalidCredentialsException
     */
    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! $user->password || ! Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $currentToken */
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }
    }

    /**
     * @return array{user: User, token: string}
     */
    public function handleGoogleCallback(SocialiteUser $socialUser): array
    {
        $user = $this->userRepository->findByGoogleId($socialUser->getId());

        if (! $user && $socialUser->getEmail()) {
            $user = $this->userRepository->findByEmail($socialUser->getEmail());
        }

        if (! $user) {
            /** @var User $user */
            $user = $this->userRepository->create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário',
                'email' => $socialUser->getEmail(),
                'google_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'password' => null,
            ]);

            $user->assignRole(UserRole::COMMON->value);
            SendWelcomeEmailJob::dispatch($user);
        } else {
            $this->userRepository->update($user, [
                'google_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->fresh(['roles', 'permissions']),
            'token' => $token,
        ];
    }

    /**
     * @return array{user: User, token: string}
     */
    public function handleSocialCallback(string $provider, SocialiteUser $socialUser): array
    {
        $user = $this->userRepository->findBySocialProvider($provider, $socialUser->getId());

        if (! $user && $socialUser->getEmail()) {
            $user = $this->userRepository->findByEmail($socialUser->getEmail());
        }

        if (! $user) {
            /** @var User $user */
            $user = $this->userRepository->create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário',
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'password' => null,
            ]);

            $user->assignRole(UserRole::COMMON->value);
            SendWelcomeEmailJob::dispatch($user);
        } else {
            $this->userRepository->update($user, [
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
        }

        $user->socialAccounts()->updateOrCreate(
            ['provider' => $provider, 'provider_id' => $socialUser->getId()],
            ['user_id' => $user->id]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->fresh(['roles', 'permissions']),
            'token' => $token,
        ];
    }

    public function sendPasswordResetLink(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            return;
        }

        $rawToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($rawToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            [
                'token' => $rawToken,
                'email' => $email,
            ]
        );

        SendMagicLinkResetJob::dispatch($user, $resetUrl);
    }

    /**
     * @throws InvalidResetTokenException
     */
    public function resetPassword(ResetPasswordDTO $dto): void
    {
        $record = DB::table('password_reset_tokens')->where('email', $dto->email)->first();

        if (! $record) {
            throw new InvalidResetTokenException;
        }

        // Verify expiration (60 mins)
        if (now()->parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $dto->email)->delete();
            throw new InvalidResetTokenException('Token expirado.');
        }

        if (! Hash::check($dto->token, $record->token)) {
            throw new InvalidResetTokenException;
        }

        $user = $this->userRepository->findByEmail($dto->email);
        if (! $user) {
            throw new InvalidResetTokenException;
        }

        $this->userRepository->update($user, [
            'password' => Hash::make($dto->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $dto->email)->delete();
    }
}
