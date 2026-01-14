<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GekySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure admin role exists
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        $user = User::firstOrCreate(
            ['email' => 'admin@gekychat.com'],
            [
                'name' => 'Geky Admin',
                'email' => 'admin@gekychat.com',
                'password' => Hash::make('Gyabaa2000;'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role using Spatie Permission
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        $this->command->info('Geky admin user created/updated successfully!');
        $this->command->info('Email: admin@gekychat.com');
        $this->command->info('Password: Gyabaa2000;');
        $this->command->info('Role: admin (Spatie Permission)');
    }
}
