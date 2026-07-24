<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            RolePermissionSeeder::class,
        );

        // create test admin user
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@demo.com',
        ]);
        $admin->assignRole('admin');

        // create test author
        $author = User::factory()->create([
            'name' => 'Author',
            'email' => 'author@demo.com',
        ]);
        $author->assignRole('author');
    }
}
