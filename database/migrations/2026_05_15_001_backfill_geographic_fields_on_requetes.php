<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remplie municipality_id / department_id sur les requêtes qui en sont dépourvues,
     * en remontant depuis la première affectation descendante (unite_admin_down).
     */
    public function up(): void
    {
        DB::statement("
            UPDATE requetes r
            JOIN (
                SELECT
                    a.requete_id,
                    ua.municipality_id,
                    ua.department_id
                FROM affectations a
                JOIN unite_admins ua ON ua.id = a.unite_admin_down
                WHERE a.sens = 1
                  AND a.id = (
                      SELECT MIN(a2.id)
                      FROM affectations a2
                      WHERE a2.requete_id = a.requete_id
                        AND a2.sens = 1
                  )
            ) geo ON geo.requete_id = r.id
            SET
                r.municipality_id = COALESCE(r.municipality_id, geo.municipality_id),
                r.department_id   = COALESCE(r.department_id,   geo.department_id)
            WHERE r.municipality_id IS NULL
              AND r.department_id   IS NULL
        ");
    }

    public function down(): void
    {
        // Pas de rollback possible sans connaître l'état d'origine champ par champ.
    }
};
