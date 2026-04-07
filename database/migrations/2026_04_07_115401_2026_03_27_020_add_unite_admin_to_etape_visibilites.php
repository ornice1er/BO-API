<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration 020 — Enrichissement etape_visibilites avec unite_admin_id
 *
 * CONTEXTE
 * ─────────
 * Après la migration 019, les rôles sont suffixés par entité
 * (Directeur_Technique_MTFP, Directeur_Technique_MS...).
 *
 * Pour une sécurité supplémentaire, on ajoute unite_admin_id sur
 * etape_visibilites — ce qui permet de filtrer non seulement par rôle
 * mais aussi par unité administrative spécifique.
 *
 * Exemple concret :
 *   - Deux Directeurs Techniques dans deux entités différentes
 *   - Même rôle Spatie générique "Directeur Technique"
 *   - La visibilité avec unite_admin_id = X ne s'applique qu'à l'agent
 *     dont l'unité administrative est X
 *
 * COMBINAISON DES DEUX APPROCHES
 * ────────────────────────────────
 * Migration 019 : rôles suffixés → distincts au niveau Spatie
 * Migration 020 : unite_admin_id → distincts au niveau unité admin
 *
 * Les deux colonnes sont complémentaires :
 *   - role_name seul     → filtre par grade/responsabilité
 *   - unite_admin_id seul→ filtre par unité (quelle que soit le grade)
 *   - les deux ensemble  → filtre précis grade + unité
 *
 * scope_type détermine quelle logique appliquer :
 *   'requete'      → filtre par role_name uniquement
 *   'unite_admin'  → filtre par role_name ET unite_admin_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etape_visibilites', function (Blueprint $table) {

            // Unité administrative spécifique (nullable = s'applique à tous)
            $table->unsignedBigInteger('unite_admin_id')
                  ->nullable()
                  ->after('role_name')
                  ->comment(
                      'Filtre optionnel par unité administrative. ' .
                      'Si NULL, la visibilité s\'applique à tous les agents du rôle. ' .
                      'Si renseigné, uniquement aux agents de cette unité.'
                  );

            $table->foreign('unite_admin_id')
                  ->references('id')
                  ->on('unite_admins')
                  ->nullOnDelete();

            $table->index('unite_admin_id', 'idx_ev_unite_admin');

            // Index composite pour le filtre getBanette()
            // (role_name, unite_admin_id, can_act)
            $table->index(
                ['role_name', 'unite_admin_id', 'can_act'],
                'idx_ev_role_ua_act'
            );
        });
    }

    public function down(): void
    {
        Schema::table('etape_visibilites', function (Blueprint $table) {
            $table->dropForeign(['unite_admin_id']);
            $table->dropIndex('idx_ev_unite_admin');
            $table->dropIndex('idx_ev_role_ua_act');
            $table->dropColumn('unite_admin_id');
        });
    }
};