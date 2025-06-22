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

        $user = User::create([
            'firstname' => 'Admin',
            'lastname' => 'Super',
            'email' => 'superadmin@gouv.bj',
            'password' => Hash::make('safsp@2024'),
            'birthdate' => '1980-01-01',
            'birthplace' => 'Cityville'
        ]);
    
        UserSetting::create([
            'user_id' => $user->id,
            'use_2FA' => false,
            'accept_notification' => false,
            'notification_list' => null,
            'mode_2FA' => 'SMS',
        ]);
       
        $user->assignRole($role);

    }
}
