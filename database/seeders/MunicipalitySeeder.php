<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use App\Utilities\Core;
use App\Models\Municipality;


class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalitiesData = [
    ['name' => 'BANIKOARA', 'department_id' => 1],
    ['name' => 'GOGOUNOU', 'department_id' => 1],
    ['name' => 'KANDI', 'department_id' => 1],
    ['name' => 'KARIMAMA', 'department_id' => 1],
    ['name' => 'MALANVILLE', 'department_id' => 1],
    ['name' => 'SEGBANA', 'department_id' => 1],
    ['name' => 'BOUKOMBE', 'department_id' => 2],
    ['name' => 'COBLY', 'department_id' => 2],
    ['name' => 'KEROU', 'department_id' => 2],
    ['name' => 'KOUANDE', 'department_id' => 2],
    ['name' => 'MATERI', 'department_id' => 2],
    ['name' => 'NATITINGOU', 'department_id' => 2],
    ['name' => 'OUASSA-PEHUNCO', 'department_id' => 2],
    ['name' => 'TANGUIETA', 'department_id' => 2],
    ['name' => 'TOUKOUNTOUNA', 'department_id' => 2],
    ['name' => 'ABOMEY-CALAVI', 'department_id' => 3],
    ['name' => 'ALLADA', 'department_id' => 3],
    ['name' => 'KPOMASSE', 'department_id' => 3],
    ['name' => 'OUIDAH', 'department_id' => 3],
    ['name' => 'SO-AVA', 'department_id' => 3],
    ['name' => 'TOFFO', 'department_id' => 3],
    ['name' => 'TORI-BOSSITO', 'department_id' => 3],
    ['name' => 'ZE', 'department_id' => 3],
    ['name' => 'BEMBEREKE', 'department_id' => 4],
    ['name' => 'KALALE', 'department_id' => 4],
    ['name' => 'N’DALI', 'department_id' => 4],
    ['name' => 'NIKKI', 'department_id' => 4],
    ['name' => 'PARAKOU', 'department_id' => 4],
    ['name' => 'PERERE', 'department_id' => 4],
    ['name' => 'SINENDE', 'department_id' => 4],
    ['name' => 'TCHAOUROU', 'department_id' => 4],
    ['name' => 'BANTE', 'department_id' => 5],
    ['name' => 'DASSA-ZOUME', 'department_id' => 5],
    ['name' => 'GLAZOUE', 'department_id' => 5],
    ['name' => 'OUESSE', 'department_id' => 5],
    ['name' => 'SAVALOU', 'department_id' => 5],
    ['name' => 'SAVE', 'department_id' => 5],
    ['name' => 'APLAHOUE', 'department_id' => 6],
    ['name' => 'DJAKOTOMEY', 'department_id' => 6],
    ['name' => 'DOGBO', 'department_id' => 6],
    ['name' => 'KLOUEKANMEY', 'department_id' => 6],
    ['name' => 'LALO', 'department_id' => 6],
    ['name' => 'TOVIKLIN', 'department_id' => 6],
    ['name' => 'BASSILA', 'department_id' => 7],
    ['name' => 'COPARGO', 'department_id' => 7],
    ['name' => 'DJOUGOU', 'department_id' => 7],
    ['name' => 'OUAKE', 'department_id' => 7],
    ['name' => 'COTONOU', 'department_id' => 8],
    ['name' => 'ATHIEME', 'department_id' => 9],
    ['name' => 'BOPA', 'department_id' => 9],
    ['name' => 'COME', 'department_id' => 9],
    ['name' => 'GRAND-POPO', 'department_id' => 9],
    ['name' => 'HOUEYOGBE', 'department_id' => 9],
    ['name' => 'LOKOSSA', 'department_id' => 9],
    ['name' => 'ADJARRA', 'department_id' => 10],
    ['name' => 'ADJOHOUN', 'department_id' => 10],
    ['name' => 'AGUEGUES', 'department_id' => 10],
    ['name' => 'AKPRO-MISSERETE', 'department_id' => 10],
    ['name' => 'AVRANKOU', 'department_id' => 10],
    ['name' => 'BONOU', 'department_id' => 10],
    ['name' => 'DANGBO', 'department_id' => 10],
    ['name' => 'PORTO-NOVO', 'department_id' => 10],
    ['name' => 'SEME-PODJI', 'department_id' => 10],
    ['name' => 'ADJA-OUERE', 'department_id' => 11],
    ['name' => 'IFANGNI', 'department_id' => 11],
    ['name' => 'KETOU', 'department_id' => 11],
    ['name' => 'POBE', 'department_id' => 11],
    ['name' => 'SAKETE', 'department_id' => 11],
    ['name' => 'ABOMEY', 'department_id' => 12],
    ['name' => 'AGBANGNIZOUN', 'department_id' => 12],
    ['name' => 'BOHICON', 'department_id' => 12],
    ['name' => 'COVE', 'department_id' => 12],
    ['name' => 'DJIDJA', 'department_id' => 12],
    ['name' => 'OUINHI', 'department_id' => 12],
    ['name' => 'ZAGNANADO', 'department_id' => 12],
    ['name' => 'ZA-KPOTA', 'department_id' => 12],
    ['name' => 'ZOGBODOMEY', 'department_id' => 12],
];


foreach ($municipalitiesData as $index => $data) {

            Municipality::create([
                'name' => $data['name'],
                'department_id' => $data['department_id'],
            ]);
        }
    }
}
