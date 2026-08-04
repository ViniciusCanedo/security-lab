<?php

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

test('handles github oauth login creating new user and social account', function () {
    $socialUser = mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->andReturn('gh-12345');
    $socialUser->shouldReceive('getName')->andReturn('GitHub Dev');
    $socialUser->shouldReceive('getEmail')->andReturn('github@example.com');
    $socialUser->shouldReceive('getAvatar')->andReturn('https://github.com/avatar.jpg');

    /** @var AuthService $service */
    $service = app(AuthService::class);
    $result = $service->handleSocialCallback('github', $socialUser);

    expect($result)->toHaveKeys(['user', 'token']);
    expect($result['user']->email)->toBe('github@example.com');

    $this->assertDatabaseHas('users', ['email' => 'github@example.com']);
    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $result['user']->id,
        'provider' => 'github',
        'provider_id' => 'gh-12345',
    ]);
});

test('handles facebook oauth login linking existing email account', function () {
    $existingUser = User::factory()->create(['email' => 'facebook@example.com']);

    $socialUser = mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->andReturn('fb-98765');
    $socialUser->shouldReceive('getName')->andReturn('FB User');
    $socialUser->shouldReceive('getEmail')->andReturn('facebook@example.com');
    $socialUser->shouldReceive('getAvatar')->andReturn('https://facebook.com/avatar.jpg');

    /** @var AuthService $service */
    $service = app(AuthService::class);
    $result = $service->handleSocialCallback('facebook', $socialUser);

    expect($result['user']->id)->toBe($existingUser->id);

    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $existingUser->id,
        'provider' => 'facebook',
        'provider_id' => 'fb-98765',
    ]);
});
