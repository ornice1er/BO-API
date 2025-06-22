<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Utilities\Core;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentNames = [
            'ALIBORI',
            'ATACORA',
            'ATLANTIQUE',
            'BORGOU',
            'COLLINES',
            'COUFFO',
            'DONGA',
            'LITTORAL',
            'MONO',
            'OUEME',
            'PLATEAU',
            'ZOU',
        ];
       foreach ($departmentNames as $index => $name) {

            Department::create([
                'name' => $name
            ]);
        }
    }
}
