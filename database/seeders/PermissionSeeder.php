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
                    'label_name' => $value2['label'],
                    'group_name' => $value2['group'],
                    'feature_name' => $value2['name'],
                    'guard_name' => 'api',
                    'name' => $value1 == 'show_edit' ? 'SHOW_EDIT_'.$value2['name'] : 'SHOW_ONLY_'.$value2['name'],
                    'show_edit' => $value1 == 'show_edit' ? true : false,
                    'show_only' => $value1 == 'show_only' ? true : false,
                ]);
            }
        }
    }
}
