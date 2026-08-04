<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

test('admin can list users with pagination and search filter', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    User::factory()->count(20)->create();

    $token = $admin->createToken('admin_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/users?per_page=10');

    $response->assertOk()
        ->assertJsonPath('meta.total', 21)
        ->assertJsonPath('meta.per_page', 10);
});

test('non-admin cannot access admin user list', function () {
    $commonUser = User::factory()->create();
    $commonUser->assignRole('COMMON');
    $token = $commonUser->createToken('user_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/users');

    $response->assertStatus(403);
});

test('admin can create a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $token = $admin->createToken('admin_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'PUBLISHER',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New User');

    $newUser = User::where('email', 'newuser@example.com')->first();
    expect($newUser->hasRole('PUBLISHER'))->toBeTrue();
});

test('admin can soft delete a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $targetUser = User::factory()->create();

    $token = $admin->createToken('admin_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/admin/users/{$targetUser->id}");

    $response->assertNoContent();
    $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
});

test('admin can change user role and action is logged in audit table', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $targetUser = User::factory()->create();
    $targetUser->assignRole('COMMON');

    $token = $admin->createToken('admin_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/admin/users/{$targetUser->id}/role", [
            'role' => 'PUBLISHER',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.roles.0', 'PUBLISHER');

    expect($targetUser->fresh()->hasRole('PUBLISHER'))->toBeTrue();

    $this->assertDatabaseHas('permission_audit_logs', [
        'actor_id' => $admin->id,
        'target_id' => $targetUser->id,
        'action' => 'role_change',
    ]);
});

test('admin can update individual user permissions and action is logged', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $targetUser = User::factory()->create();
    $targetUser->assignRole('COMMON');

    $token = $admin->createToken('admin_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/admin/users/{$targetUser->id}/permissions", [
            'permissions' => ['article.create'],
            'action' => 'grant',
        ]);

    $response->assertOk();
    expect($targetUser->fresh()->hasDirectPermission('article.create'))->toBeTrue();

    $this->assertDatabaseHas('permission_audit_logs', [
        'actor_id' => $admin->id,
        'target_id' => $targetUser->id,
        'action' => 'permission_grant',
    ]);
});
