<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default roles
        $roles = ['admin', 'oracle', 'viewer'];
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        }

        // Create default users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Oracle User',
                'email' => 'oracle@example.com',
                'password' => bcrypt('password'),
                'role' => 'oracle',
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'password' => bcrypt('password'),
                'role' => 'viewer',
            ],
        ];

        foreach ($users as $userData) {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            // Assign role via Spatie permission
            $user->assignRole($userData['role']);
        }
    }
}
