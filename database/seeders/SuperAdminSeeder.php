<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use App\Models\UserProject;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);

        $user = User::firstOrCreate(
            ['email' => 'superadmin@gouv.bj'],
            [
                'username' => 'Super Admin',
                'password' => Hash::make('boes@2025'),
            ]
        );

        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'use_2FA' => false,
                'accept_notification' => false,
                'notification_list' => null,
                'mode_2FA' => 'SMS',
            ]
        );

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

    }
}
