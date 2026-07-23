<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached role and permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permission
        $permissions = [
            'create posts',
            'edit own posts',
            'edit all posts',
            'delete own posts',
            'delete all posts',
            'publish posts',
            'manage user',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // create role and assign permission
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = Role::create(['name' => 'editor']);
        $editorRole->givePermissionTo([
            'create posts',
            'edit all posts',
            'delete all posts',
            'publish posts',
        ]);

        $authorRole = Role::create(['name' => 'author']);
        $authorRole->givePermissionTo([
            'create posts',
            'edit own posts',
            'delete own posts',
        ]);

        $subscriberRole = Role::create(['name' => 'subscriber']);
        // only read permission,subscriber has.
    }
}
