<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@45seconds.test'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $superAdmin = Role::where('name', Role::SUPER_ADMIN)->first();
        if ($superAdmin) {
            $admin->roles()->syncWithoutDetaching([$superAdmin->id]);
        }

        $staffUser = User::updateOrCreate(
            ['email' => 'staff@45seconds.test'],
            [
                'name' => 'موظف المبيعات',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $staffRole = Role::where('name', Role::STAFF)->first();
        if ($staffRole) {
            $staffUser->roles()->syncWithoutDetaching([$staffRole->id]);
        }
    }
}
