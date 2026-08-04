<?php

use App\Jobs\SendMagicLinkResetJob;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

test('guest can register with valid credentials and receive token and welcome email', function () {
    Queue::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'roles', 'permissions'],
                'token',
            ],
            'meta' => ['message'],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->hasRole('COMMON'))->toBeTrue();

    Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });
});

test('registration validation fails with invalid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('secret123'),
    ]);
    $user->assignRole('COMMON');

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['user', 'token'],
            'meta' => ['message'],
        ]);
});

test('login fails with wrong credentials', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can logout and revoke token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertNoContent();
    expect($user->tokens()->count())->toBe(0);
});

test('authenticated user can access /me', function () {
    $user = User::factory()->create();
    $user->assignRole('COMMON');
    $token = $user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

test('unauthenticated request to /me fails with 401', function () {
    $response = $this->getJson('/api/v1/me');
    $response->assertStatus(401);
});

test('google oauth login creates user if missing', function () {
    Queue::fake();

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-123456');
    $socialiteUser->shouldReceive('getName')->andReturn('Google User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('google@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
    $socialiteUser->shouldReceive('getNickname')->andReturn(null);

    $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $provider->shouldReceive('stateless->user')->andReturn($socialiteUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/google/callback');

    $response->assertOk()
        ->assertJsonPath('data.user.email', 'google@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'google@example.com',
        'google_id' => 'google-123456',
    ]);
});

test('forgot password queues magic link reset job', function () {
    Queue::fake();

    $user = User::factory()->create(['email' => 'resetme@example.com']);

    $response = $this->postJson('/api/v1/auth/password/forgot', [
        'email' => 'resetme@example.com',
    ]);

    $response->assertOk();

    Queue::assertPushed(SendMagicLinkResetJob::class, function ($job) use ($user) {
        return $job->user->id === $user->id && str_contains($job->resetUrl, 'token=');
    });
});

test('password reset succeeds with valid token', function () {
    $user = User::factory()->create(['email' => 'resetme2@example.com', 'password' => Hash::make('oldpassword')]);
    $token = 'valid-reset-token-123';

    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/auth/password/reset', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertOk();
    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();
});
