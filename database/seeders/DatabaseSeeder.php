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

        // Create an initial governance setting if none exists.  This
        // prevents the dashboard from showing blank values on a fresh
        // installation.  The latest record will always be considered
        // active; users can update via the UI.
        if (\App\Models\GovernanceSetting::count() === 0) {
            \App\Models\GovernanceSetting::create([
                'overlay_tier'     => 'none',
                'expected_rate'    => 0,
                'max_oracle_delay' => 300,
                'use_curve'        => false,
                'uni_fee_tier'     => 3000,
                'curve_index_in'   => 1,
                'curve_index_out'  => 0,
                'loan_asset'       => null,
                'profit_wallet'    => null,
                'fallback_wallet'  => null,
            ]);
        }
    }
}
