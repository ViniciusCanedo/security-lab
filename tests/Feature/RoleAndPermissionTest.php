<?php

use App\Enums\UserRole;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('it seeds roles and permissions correctly', function () {
    // Assert roles exist
    expect(Role::where('name', UserRole::COMMON->value)->exists())->toBeTrue();
    expect(Role::where('name', UserRole::PUBLISHER->value)->exists())->toBeTrue();
    expect(Role::where('name', UserRole::ADMIN->value)->exists())->toBeTrue();

    // Get roles
    $commonRole = Role::findByName(UserRole::COMMON->value);
    $publisherRole = Role::findByName(UserRole::PUBLISHER->value);
    $adminRole = Role::findByName(UserRole::ADMIN->value);

    // Verify COMMON permissions
    expect($commonRole->hasPermissionTo('article.view'))->toBeTrue();
    expect($commonRole->hasPermissionTo('article.like'))->toBeTrue();
    expect($commonRole->hasPermissionTo('comment.create'))->toBeTrue();
    expect($commonRole->hasPermissionTo('comment.reply'))->toBeTrue();
    expect($commonRole->hasPermissionTo('article.create'))->toBeFalse();

    // Verify PUBLISHER permissions
    expect($publisherRole->hasPermissionTo('article.create'))->toBeTrue();
    expect($publisherRole->hasPermissionTo('article.edit.own'))->toBeTrue();
    expect($publisherRole->hasPermissionTo('article.archive.own'))->toBeTrue();
    expect($publisherRole->hasPermissionTo('newsletter.manage'))->toBeTrue();
    expect($publisherRole->hasPermissionTo('article.delete'))->toBeFalse();

    // Verify ADMIN permissions
    expect($adminRole->hasPermissionTo('article.edit.any'))->toBeTrue();
    expect($adminRole->hasPermissionTo('article.archive.any'))->toBeTrue();
    expect($adminRole->hasPermissionTo('article.delete'))->toBeTrue();
    expect($adminRole->hasPermissionTo('comment.moderate'))->toBeTrue();
    expect($adminRole->hasPermissionTo('user.invite'))->toBeTrue();
    expect($adminRole->hasPermissionTo('user.remove'))->toBeTrue();
    expect($adminRole->hasPermissionTo('user.promote'))->toBeTrue();
});
