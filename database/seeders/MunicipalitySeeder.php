<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::insert("INSERT INTO `municipalities` (`name`, `department_id`) VALUES
('BANIKOARA', 1),
('GOGOUNOU', 1),
('KANDI', 1),
('KARIMAMA', 1),
('MALANVILLE', 1),
('SEGBANA', 1),
('BOUKOMBE', 2),
('COBLY', 2),
('KEROU', 2),
('KOUANDE', 2),
('MATERI', 2),
('NATITINGOU', 2),
('OUASSA-PEHUNCO', 2),
('TANGUIETA', 2),
('TOUKOUNTOUNA', 2),
('ABOMEY-CALAVI', 3),
('ALLADA', 3),
('KPOMASSE', 3),
('OUIDAH', 3),
('SO-AVA', 3),
('TOFFO', 3),
('TORI-BOSSITO', 3),
('ZE', 3),
('BEMBEREKE', 4),
('KALALE', 4),
('N’DALI', 4),
('NIKKI', 4),
('PARAKOU', 4),
('PERERE', 4),
('SINENDE', 4),
('TCHAOUROU', 4),
('BANTE', 5),
('DASSA-ZOUME', 5),
('GLAZOUE', 5),
('OUESSE', 5),
('SAVALOU', 5),
('SAVE', 5),
('APLAHOUE', 6),
('DJAKOTOMEY', 6),
('DOGBO', 6),
('KLOUEKANMEY', 6),
('LALO', 6),
('TOVIKLIN', 6),
('BASSILA', 7),
('COPARGO', 7),
('DJOUGOU', 7),
('OUAKE', 7),
('COTONOU', 8),
('ATHIEME', 9),
('BOPA', 9),
('COME', 9),
('GRAND-POPO', 9),
('HOUEYOGBE', 9),
('LOKOSSA', 9),
('ADJARRA', 10),
('ADJOHOUN', 10),
('AGUEGUES', 10),
('AKPRO-MISSERETE', 10),
('AVRANKOU', 10),
('BONOU', 10),
('DANGBO', 10),
('PORTO-NOVO', 10),
('SEME-PODJI', 10),
('ADJA-OUERE', 11),
('IFANGNI', 11),
('KETOU', 11),
('POBE', 11),
('SAKETE', 11),
('ABOMEY', 12),
('AGBANGNIZOUN', 12),
('BOHICON', 12),
('COVE', 12),
('DJIDJA', 12),
('OUINHI', 12),
('ZAGNANADO', 12),
('ZA-KPOTA', 12),
('ZOGBODOMEY', 12);");
    }
}
