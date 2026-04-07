<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration 019 — Rôles Spatie distincts par entité métier
 *
 * CONTEXTE
 * ─────────
 * L'architecture hiérarchique est identique dans chaque entité :
 *   Ministre > Directeur Général > Directeur Technique > Chef Service > Chef Division > Collaborateur
 *
 * Problème identifié lors des tests :
 *   Le rôle "Directeur Technique" existe dans deux entités distinctes :
 *     - MTFP : Directeur Technique = DSSMST (traitement agrément)
 *     - MS   : Directeur Technique = DNSP   (prévalidation agrément)
 *
 *   Avec un rôle Spatie unique "Directeur Technique", il est impossible
 *   de distinguer les deux dans etape_visibilites — les deux agents
 *   verraient les mêmes banettes et pourraient agir sur les mêmes étapes.
 *
 * SOLUTION
 * ─────────
 * Créer des rôles Spatie suffixés par l'entité pour chaque grade.
 * Les rôles génériques (sans suffixe) sont conservés pour rétrocompatibilité.
 *
 * ENTITÉS COUVERTES
 * ──────────────────
 * MTFP : Ministère du Travail et de la Fonction Publique
 *   - Ministre
 *   - Directeur_General_MTFP  (DGT)
 *   - Directeur_Technique_MTFP (DSSMST)
 *   - Chef_Service_MTFP
 *   - Chef_Division_MTFP
 *   - Collaborateur_MTFP
 *
 * MS : Ministère de la Santé
 *   - Directeur_Technique_MS  (DNSP)
 *   - Chef_Service_MS
 *   - Chef_Division_MS
 *   - Collaborateur_MS
 *
 * NOTE : Le rôle "Ministre" reste unique — il n'y a qu'un Ministre du Travail.
 */
return new class extends Migration
{
    // Rôles à créer — suffixés par entité
    private array $roles = [
        // MTFP
        'Ministre',                  // unique — pas de suffixe
        'Directeur_General_MTFP',    // DGT
        'Directeur_Technique_MTFP',  // DSSMST
        'Chef_Service_MTFP',
        'Chef_Division_MTFP',
        'Collaborateur_MTFP',
        // MS
        'Directeur_General_MS',
        'Directeur_Technique_MS',    // DNSP
        'Chef_Service_MS',
        'Chef_Division_MS',
        'Collaborateur_MS',
        // Administrateur (transversal)
        'Administrateur_Sectoriel',
        'Super_Admin',
    ];

    public function up(): void
    {
        // 1. Créer les nouveaux rôles Spatie s'ils n'existent pas
        foreach ($this->roles as $roleName) {
            DB::table('roles')->insertOrIgnore([
                'name'       => $roleName,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Mettre à jour les visibilités existantes
        //    "Chef Division" → "Directeur_General_MTFP" (DGT paraphe)
        //    car Chef Division ≠ DGT dans l'organigramme
        DB::table('etape_visibilites')
            ->where('role_name', 'Chef Division')
            ->update([
                'role_name'  => 'Directeur_General_MTFP',
                'updated_at' => now(),
            ]);

        // 3. Mettre à jour "Directeur Technique" → "Directeur_Technique_MTFP"
        //    pour distinguer DSSMST (MTFP) de DNSP (MS)
        DB::table('etape_visibilites')
            ->where('role_name', 'Directeur Technique')
            ->update([
                'role_name'  => 'Directeur_Technique_MTFP',
                'updated_at' => now(),
            ]);

        // 4. Mettre à jour "Directeur Général" → "Directeur_General_MTFP"
        DB::table('etape_visibilites')
            ->where('role_name', 'Directeur Général')
            ->update([
                'role_name'  => 'Directeur_General_MS',
                'updated_at' => now(),
            ]);

        // 5. Ajouter les visibilités DNSP manquantes avec le bon rôle
        //    Transitions 7 (Signature Ministre → DNSP) et 9 (DNSP → Rejet)
        $prestationId = DB::table('prestations')
            ->where('code', 'PS00569')
            ->value('id');

        if ($prestationId) {
            // Transition 7 : Signature Ministre lettre → Prévalidation DNSP
            $t7 = DB::table('workflow_transitions as wt')
                ->join('etapes as ef', 'ef.id', '=', 'wt.etape_from_id')
                ->join('etapes as et', 'et.id', '=', 'wt.etape_to_id')
                ->where('wt.prestation_id', $prestationId)
                ->where('ef.name', 'Signature projet de lettre')
                ->where('et.name', 'Prévalidation DNSP')
                ->value('wt.id');

            // Transition 9 : Prévalidation DNSP → Rejet
            $t9 = DB::table('workflow_transitions as wt')
                ->join('etapes as ef', 'ef.id', '=', 'wt.etape_from_id')
                ->where('wt.prestation_id', $prestationId)
                ->where('ef.name', 'Prévalidation DNSP')
                ->where('wt.condition_type', 'rejet')
                ->value('wt.id');

            $visibilites = [];

            if ($t7) {
                $visibilites[] = [
                    'workflow_transition_id' => $t7,
                    'role_name'  => 'Directeur_Technique_MS',  // DNSP
                    'can_read'   => 1,
                    'can_act'    => 1,
                    'scope_type' => 'requete',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($t9) {
                $visibilites[] = [
                    'workflow_transition_id' => $t9,
                    'role_name'  => 'Directeur_Technique_MS',
                    'can_read'   => 1,
                    'can_act'    => 1,
                    'scope_type' => 'requete',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach ($visibilites as $v) {
                DB::table('etape_visibilites')->insertOrIgnore($v);
            }
        }

        // 6. Log de synthèse
        $total = DB::table('roles')->whereIn('name', $this->roles)->count();
        \Log::info("Migration 019 : {$total} rôles métier configurés.");
    }

    public function down(): void
    {
        // Supprimer les rôles suffixés créés par cette migration
        // (ne pas supprimer les rôles génériques existants)
        $rolesSuffixes = array_filter($this->roles, fn($r) =>
            str_contains($r, '_MTFP') || str_contains($r, '_MS')
        );

        DB::table('roles')->whereIn('name', $rolesSuffixes)->delete();

        // Restaurer les anciens noms de rôles dans etape_visibilites
        DB::table('etape_visibilites')
            ->where('role_name', 'Directeur_General_MTFP')
            ->update(['role_name' => 'Chef Division', 'updated_at' => now()]);

        DB::table('etape_visibilites')
            ->where('role_name', 'Directeur_Technique_MTFP')
            ->update(['role_name' => 'Directeur Technique', 'updated_at' => now()]);

        DB::table('etape_visibilites')
            ->where('role_name', 'Directeur_General_MS')
            ->update(['role_name' => 'Directeur Général', 'updated_at' => now()]);

        DB::table('etape_visibilites')
            ->where('role_name', 'Directeur_Technique_MS')
            ->delete();
    }
};