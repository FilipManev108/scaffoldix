<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Demo User', 'email' => 'admin@demo.test'],
            ['name' => 'Team Lead Demo User', 'email' => 'teamlead@demo.test'],
            ['name' => 'Senior Demo User', 'email' => 'senior@demo.test'],
            ['name' => 'Mid Demo User', 'email' => 'mid@demo.test'],
            ['name' => 'Junior Demo User', 'email' => 'junior@demo.test'],
            ['name' => 'Viewer Demo User', 'email' => 'viewer@demo.test'],
        ];

        foreach ($users as $userData) {
            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                ],
            );

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }
    }
}
