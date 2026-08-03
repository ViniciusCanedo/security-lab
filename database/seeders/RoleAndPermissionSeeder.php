<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // Articles
            'article.view',
            'article.like',
            'article.create',
            'article.edit.own',
            'article.edit.any',
            'article.archive.own',
            'article.archive.any',
            'article.delete',

            // Comments
            'comment.create',
            'comment.reply',
            'comment.moderate',

            // User Management
            'user.invite',
            'user.remove',
            'user.promote',

            // Newsletter
            'newsletter.manage',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign existing permissions

        // COMMON Role
        $commonRole = Role::create(['name' => UserRole::COMMON->value, 'guard_name' => 'web']);
        $commonRole->givePermissionTo([
            'article.view',
            'article.like',
            'comment.create',
            'comment.reply',
        ]);

        // PUBLISHER Role
        $publisherRole = Role::create(['name' => UserRole::PUBLISHER->value, 'guard_name' => 'web']);
        $publisherRole->givePermissionTo([
            'article.view',
            'article.like',
            'comment.create',
            'comment.reply',
            'article.create',
            'article.edit.own',
            'article.archive.own',
            'newsletter.manage',
        ]);

        // ADMIN Role
        $adminRole = Role::create(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::all());
    }
}
