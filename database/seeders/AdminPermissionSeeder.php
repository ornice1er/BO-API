<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Permissions déclaratives utilisées pour identifier le niveau d'accès admin.
     * Remplace les vérifications par nom de rôle (ex: "Admin national") dans le code.
     *
     * access:admin-global   → accès illimité (Admin national, Super Admin)
     * access:admin-sectoriel → accès sectoriel (Admin Sectoriel)
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $global    = Permission::firstOrCreate(
            ['name' => 'access:admin-global',    'guard_name' => 'api'],
            ['feature_name' => 'admin_access']
        );
        $sectoriel = Permission::firstOrCreate(
            ['name' => 'access:admin-sectoriel', 'guard_name' => 'api'],
            ['feature_name' => 'admin_access']
        );

        // Rôles qui héritent de l'accès global
        $globalRoles = ['Admin national', 'Super Admin', 'Super_Admin'];
        foreach ($globalRoles as $name) {
            $role = Role::where('name', $name)->where('guard_name', 'api')->first();
            if ($role && ! $role->hasPermissionTo($global)) {
                $role->givePermissionTo($global);
            }
        }

        // Rôles qui héritent de l'accès sectoriel
        $sectorielRoles = ['Admin Sectoriel', 'Administrateur_Sectoriel', 'Administrateur Sectoriel'];
        foreach ($sectorielRoles as $name) {
            $role = Role::where('name', $name)->where('guard_name', 'api')->first();
            if ($role && ! $role->hasPermissionTo($sectoriel)) {
                $role->givePermissionTo($sectoriel);
            }
        }
    }
}
