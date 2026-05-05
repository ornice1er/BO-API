<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Department;
use App\Models\Municipality;
use App\Models\UniteAdmin;
use App\Models\UserPrestation;
use App\Models\UserSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class MEMPUniteAdminSeeder extends Seeder
{
    // IDs stables dans la base
    const ENTITE_ADMIN_ID    = 7; // Ministère de l'Enseignement Maternelle et Primaire
    const TYPE_DDEMP         = 8; // Direction Départementale
    const TYPE_CS            = 3; // Service
    const FONCTION_DIRECTEUR = 7; // Directeur
    const FONCTION_CHEF_SVC  = 3; // Chef Service
    const PASSWORD           = 'boes@2025';

    /** Codes des prestations à attribuer à chaque compte */
    const PRESTATION_CODES = ['PS00702', 'PS00706', 'PS00707', 'PS00709', 'PS00710'];

    public function run(): void
    {
        // Idempotence : skip si des DDEMP MEMP réels (avec département) existent déjà
        if (UniteAdmin::where('entite_admin_id', self::ENTITE_ADMIN_ID)
                       ->where('type_unite_admin_id', self::TYPE_DDEMP)
                       ->whereNotNull('department_id')
                       ->exists()) {
            $this->command->info('MEMPUniteAdminSeeder : données déjà présentes, skip.');
            return;
        }

        $prestations = \App\Models\Prestation::whereIn('code', self::PRESTATION_CODES)
                        ->pluck('id');

        $roleDirecteur = Role::firstOrCreate(['name' => 'Directeur',  'guard_name' => 'api']);
        $roleCCD       = Role::firstOrCreate(['name' => 'CCD',        'guard_name' => 'api']);

        $depts = Department::orderBy('id')->get();
        $muns  = Municipality::orderBy('department_id')->orderBy('name')->get();

        // ─── 1. DDEMP par département ────────────────────────────────────────────
        $ddempByDept = []; // dept_id → UniteAdmin

        foreach ($depts as $dept) {
            $slug    = $this->emailSlug($dept->name);
            $email   = "ddemp{$slug}@gouv.bj";
            $libelle = "Direction Départementale de l'Enseignement Maternel et Primaire de {$dept->name}";
            $sigle   = "DDEMP-{$slug}";

            $ddemp = UniteAdmin::firstOrCreate(
                ['email' => $email],
                [
                    'libelle'             => $libelle,
                    'sigle'              => $sigle,
                    'type_unite_admin_id' => self::TYPE_DDEMP,
                    'entite_admin_id'     => self::ENTITE_ADMIN_ID,
                    'department_id'       => $dept->id,
                    'ua_parent_code'      => null,
                ]
            );

            $ddempByDept[$dept->id] = $ddemp;

            $this->createUserForUA($ddemp, $email, $dept->name, 'DDEMP',
                self::FONCTION_DIRECTEUR, $roleDirecteur, $prestations);
        }

        // ─── 2. CS par commune ───────────────────────────────────────────────────
        foreach ($muns as $mun) {
            $slug    = $this->emailSlug($mun->name);
            $email   = "cs{$slug}@gouv.bj";
            $libelle = "Circonscription Scolaire de {$mun->name}";
            $sigle   = "CS-{$slug}";
            $parent  = $ddempByDept[$mun->department_id] ?? null;

            $cs = UniteAdmin::firstOrCreate(
                ['email' => $email],
                [
                    'libelle'             => $libelle,
                    'sigle'              => $sigle,
                    'type_unite_admin_id' => self::TYPE_CS,
                    'entite_admin_id'     => self::ENTITE_ADMIN_ID,
                    'department_id'       => $mun->department_id,
                    'municipality_id'     => $mun->id,
                    'ua_parent_code'      => $parent?->id,
                ]
            );

            $this->createUserForUA($cs, $email, $mun->name, 'CS',
                self::FONCTION_CHEF_SVC, $roleCCD, $prestations);
        }

        $total = count($depts) + count($muns);
        $this->command->info("MEMPUniteAdminSeeder : {$total} unités créées.");
    }

    private function createUserForUA(
        UniteAdmin $ua,
        string     $email,
        string     $placeName,
        string     $type,
        int        $fonctionId,
        Role       $role,
        $prestations
    ): void {
        $fullName = "{$type} {$placeName}";

        $matricule = strtoupper($type) . '-' . Str::upper(Str::slug($placeName, ''));

        $agent = Agent::firstOrCreate(
            ['unite_admin_id' => $ua->id],
            [
                'lastname'          => $type,
                'firstname'         => $placeName,
                'numero_matricule'  => $matricule,
                'entite_admin_id'   => self::ENTITE_ADMIN_ID,
                'fonction_agent_id' => $fonctionId,
            ]
        );

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'username'        => $fullName,
                'name'            => $fullName,
                'password'        => Hash::make(self::PASSWORD),
                'agent_id'        => $agent->id,
                'entite_admin_id' => self::ENTITE_ADMIN_ID,
                'is_active'       => true,
                'first_signin'    => true,
            ]
        );

        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'use_2FA'              => false,
                'accept_notification'  => false,
                'notification_list'    => null,
                'mode_2FA'             => 'SMS',
            ]
        );

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        foreach ($prestations as $prestId) {
            UserPrestation::firstOrCreate([
                'user_id'       => $user->id,
                'prestation_id' => $prestId,
            ]);
        }
    }

    private function emailSlug(string $name): string
    {
        return Str::slug($name, ''); // retire accents, espaces, tirets → "abomeycalavi"
    }
}
