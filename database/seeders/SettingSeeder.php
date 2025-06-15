<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('fct_setting.keys') as $key) {
            info($key);
            Setting::updateOrCreate(['key' => $key]);
        }
    }
}
