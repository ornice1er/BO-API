<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 1/6 — Enrichissement de la table `etapes`
 *
 * La table existante ne contient que `name`.
 * On lui ajoute les métadonnées nécessaires au moteur de workflow :
 *  - type          : phase métier de l'étape (dépôt, traitement, commission, délivrance)
 *  - unite_admin_id: unité administrative responsable de cette étape
 *  - is_terminal   : indique si l'étape clôture la demande (pas de transition sortante)
 *  - allow_partial_save : autorise la sauvegarde partielle (flux alternatif FA2)
 *  - sla_days      : délai réglementaire maximum en jours pour traiter cette étape
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            $table->enum('type', ['depot', 'traitement', 'commission', 'delivrance'])
                  ->default('traitement')
                  ->after('name')
                  ->comment('Phase métier de l\'étape');

            $table->unsignedBigInteger('unite_admin_id')
                  ->nullable()
                  ->after('type')
                  ->comment('Unité administrative responsable du traitement');

            $table->boolean('is_terminal')
                  ->default(false)
                  ->after('unite_admin_id')
                  ->comment('Vrai si l\'étape clôture définitivement la demande');

            $table->boolean('allow_partial_save')
                  ->default(false)
                  ->after('is_terminal')
                  ->comment('Autorise la sauvegarde partielle du formulaire (FA2)');

            $table->unsignedInteger('sla_days')
                  ->nullable()
                  ->after('allow_partial_save')
                  ->comment('Délai réglementaire maximum en jours');

            $table->foreign('unite_admin_id')
                  ->references('id')
                  ->on('unite_admins')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            $table->dropForeign(['unite_admin_id']);
            $table->dropColumn([
                'type',
                'unite_admin_id',
                'is_terminal',
                'allow_partial_save',
                'sla_days',
            ]);
        });
    }
};
