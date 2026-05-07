<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Department;
use App\Models\EntiteAdmin;
use App\Models\FonctionAgent;
use App\Models\Municipality;
use App\Models\TypeUniteAdmin;
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
    // Libellés de référence — résolus dynamiquement à l'exécution
    const ENTITE_LIBELLE         = 'Ministère de l\'Enseignement Maternelle et Primaire';
    const TYPE_DDEMP_LIBELLE     = 'Direction Départementale';
    const TYPE_CS_LIBELLE        = 'Circonscription Scolaire';
    const FONCTION_DIR_LIBELLE   = 'Directeur';
    const FONCTION_CCD_LIBELLE   = 'Chef de Circonscription';
    const PASSWORD               = 'boes@2025';

    /** Codes des prestations à attribuer à chaque compte */
    const PRESTATION_CODES = ['PS00702', 'PS00706', 'PS00707', 'PS00709', 'PS00710'];

    public function run(): void
    {
        // Résolution dynamique des références
        $entite = EntiteAdmin::where('libelle', self::ENTITE_LIBELLE)->first();
        if (!$entite) {
            $this->command->warn('MEMPUniteAdminSeeder : entité "' . self::ENTITE_LIBELLE . '" introuvable, skip.');
            return;
        }

        $typeDdemp = TypeUniteAdmin::firstOrCreate(
            ['libelle' => self::TYPE_DDEMP_LIBELLE]
        );
        $typeCs = TypeUniteAdmin::firstOrCreate(
            ['libelle' => self::TYPE_CS_LIBELLE]
        );

        $fonctionDir = FonctionAgent::firstOrCreate(
            ['libelle' => self::FONCTION_DIR_LIBELLE]
        );
        $fonctionCcd = FonctionAgent::firstOrCreate(
            ['libelle' => self::FONCTION_CCD_LIBELLE]
        );

        // Idempotence : skip seulement si DDEMP et CS sont déjà tous présents
        $ddempExist = UniteAdmin::where('entite_admin_id', $entite->id)
                                ->where('type_unite_admin_id', $typeDdemp->id)
                                ->whereNotNull('department_id')
                                ->exists();
        $csExist    = UniteAdmin::where('entite_admin_id', $entite->id)
                                ->where('type_unite_admin_id', $typeCs->id)
                                ->whereNull('department_id')
                                ->whereNotNull('municipality_id')
                                ->exists();
        if ($ddempExist && $csExist) {
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
                    'type_unite_admin_id' => $typeDdemp->id,
                    'entite_admin_id'     => $entite->id,
                    'department_id'       => $dept->id,
                    'ua_parent_code'      => null,
                ]
            );

            $ddempByDept[$dept->id] = $ddemp;

            $this->createUserForUA($ddemp, $email, $dept->name, 'DDEMP',
                $fonctionDir->id, $roleDirecteur, $prestations);
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
                    'type_unite_admin_id' => $typeCs->id,
                    'entite_admin_id'     => $entite->id,
                    'department_id'       => null,
                    'municipality_id'     => $mun->id,
                    'ua_parent_code'      => $parent?->id,
                ]
            );

            $this->createUserForUA($cs, $email, $mun->name, 'CS',
                $fonctionCcd->id, $roleCCD, $prestations);
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
                'entite_admin_id'   => $ua->entite_admin_id,
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
                'entite_admin_id' => $ua->entite_admin_id,
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
