<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dépréciation de colonnes legacy de la table `prestations`.
 *
 * Logique désormais portée par le moteur granulaire (étapes / transitions / circuit) :
 *   - need_validation        → transition condition_type = 'validation'
 *   - need_meeting (colonne)  → accesseur getNeedMeetingAttribute (calculé via etape.need_meeting)
 *   - has_document_circuit    → existence d'un EtapeDocumentProduit / document-circuit-etapes
 *   - is_automatic_delivered  → transition 'auto' / étape terminale
 *   - needOut                 → obsolète
 *   - eps_id                  → FK orpheline (table etape_prestation_statuses supprimée)
 *
 * ⚠️ À NE LANCER QU'APRÈS déploiement du code nettoyé (frontend + validation).
 *    Sinon le code déployé qui écrit encore ces colonnes provoquera
 *    « Unknown column ... » (cf. incident pris_en_charge).
 *
 * Conservés (encore utiles / optionnels) : signer, delay, decision,
 * is_group_delivered, start_point, from_pns, content_type, paiement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestations', function (Blueprint $table) {
            foreach (['needOut', 'need_validation', 'need_meeting', 'has_document_circuit', 'is_automatic_delivered', 'eps_id'] as $col) {
                if (Schema::hasColumn('prestations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('prestations', function (Blueprint $table) {
            if (!Schema::hasColumn('prestations', 'needOut'))                $table->boolean('needOut')->default(false);
            if (!Schema::hasColumn('prestations', 'need_validation'))        $table->boolean('need_validation')->default(false);
            if (!Schema::hasColumn('prestations', 'need_meeting'))           $table->boolean('need_meeting')->default(false);
            if (!Schema::hasColumn('prestations', 'has_document_circuit'))   $table->boolean('has_document_circuit')->default(false);
            if (!Schema::hasColumn('prestations', 'is_automatic_delivered')) $table->boolean('is_automatic_delivered')->default(false);
            if (!Schema::hasColumn('prestations', 'eps_id'))                 $table->unsignedBigInteger('eps_id')->nullable();
        });
    }
};
