<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
              foreach (Config('fct_setting.actions') as $value1) {
            foreach (Config('fct_setting.features') as $value2) {
                Permission::updateOrCreate([
                    'feature_name' => $value2,
                    'guard_name' => 'api',
                    'name' =>$value1. " ".$value2,
                ]);
            }
        }
    }
}
