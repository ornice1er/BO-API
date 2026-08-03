<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attestation (PS00926) délivrée automatiquement à la suite d'un stage (PS00928).
 *
 * - requetes.rapport_stage_path / _uploaded_at : rapport de stage déposé par un
 *   agent sur une demande de stage (ex. PS00928), même clôturée.
 *
 * - prestations.source_prestation_id : prestation prérequise dont la demande
 *   doit être aboutie (ex. PS00926 dépend de PS00928). Sa PRÉSENCE active la
 *   délivrance automatique (le drapeau `is_automatic_delivered` a été retiré du
 *   schéma par 2026_06_10_003 ; on ne s'appuie donc PAS dessus).
 * - prestations.reference_field_key : clé du champ de `step_contents` où le
 *   demandeur fournit la référence de la demande source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (!Schema::hasColumn('requetes', 'rapport_stage_path')) {
                $table->string('rapport_stage_path')->nullable()->after('structure_id');
            }
            if (!Schema::hasColumn('requetes', 'rapport_stage_uploaded_at')) {
                $table->timestamp('rapport_stage_uploaded_at')->nullable()->after('rapport_stage_path');
            }
        });

        Schema::table('prestations', function (Blueprint $table) {
            // Pas de `after('is_automatic_delivered')` : cette colonne a été supprimée
            // du schéma (2026_06_10_003) et n'existe pas sur tous les environnements.
            if (!Schema::hasColumn('prestations', 'source_prestation_id')) {
                $table->foreignId('source_prestation_id')->nullable();
            }
            if (!Schema::hasColumn('prestations', 'reference_field_key')) {
                $table->string('reference_field_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            foreach (['rapport_stage_path', 'rapport_stage_uploaded_at'] as $c) {
                if (Schema::hasColumn('requetes', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('prestations', function (Blueprint $table) {
            foreach (['source_prestation_id', 'reference_field_key'] as $c) {
                if (Schema::hasColumn('prestations', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
