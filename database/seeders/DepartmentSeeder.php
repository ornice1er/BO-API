<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::insert("INSERT INTO `departments` (`code`, `name`,`country_id`) VALUES
            ('01', 'ALIBORI',1),
            ('02', 'ATACORA',1),
            ('03', 'ATLANTIQUE',1),
            ('04', 'BORGOU',1),
            ('05', 'COLLINES',1),
            ('06', 'COUFFO',1),
            ('07', 'DONGA',1),
            ('08', 'LITTORAL',1),
            ('09', 'MONO',1),
            ('10', 'OUEME',1),
            ('11', 'PLATEAU',1),
            ('12', 'ZOU',1)");
    }
}
