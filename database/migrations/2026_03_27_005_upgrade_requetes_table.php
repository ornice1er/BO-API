<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 5/6 — Enrichissement de la table `requetes`
 *
 * Trois ajouts essentiels pour brancher les requêtes sur le nouveau moteur :
 *
 * 1. current_etape_id
 *    Le pointeur courant dans le workflow. À tout moment, on sait exactement
 *    où en est la demande dans son parcours, sans parser des flags booléens
 *    (isTreated, isAutorized, isFinished, isDeclined...).
 *    → Remplace à terme la logique dispersée dans plusieurs colonnes boolean.
 *
 * 2. current_status_id
 *    FK normalisée vers `statuses`. Remplace `status INT` (sans contrainte).
 *    On garde la colonne `status` existante en parallèle le temps de la
 *    migration des données, puis on la supprime dans une migration ultérieure
 *    après validation.
 *
 * 3. step_data (JSON)
 *    Remplace content / content2 / content3 (colonnes longtext génériques).
 *    Stocke les données spécifiques à chaque étape sous forme de JSON structuré.
 *    Exemple : {"depot": {"sigle": "CFPB", "domaines": ["informatique"]},
 *               "visite": {"date_visite": "2026-04-10", "rapport": "rapports/xyz.pdf"}}
 *    → Les colonnes content/content2/content3 sont conservées pour compatibilité
 *      ascendante et supprimées après migration des données existantes.
 *
 * NOTE : requetes utilise ENGINE=MyISAM dans le dump existant.
 * Les FK ne fonctionnent pas sur MyISAM. On convertit en InnoDB ici.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Conversion MyISAM → InnoDB pour activer les FK
        \DB::statement('ALTER TABLE requetes ENGINE=InnoDB');

        Schema::table('requetes', function (Blueprint $table) {

            // Pointeur d'étape courante
            $table->unsignedBigInteger('current_etape_id')
                  ->nullable()
                  ->after('prestation_id')
                  ->comment('Étape courante dans le workflow (FK etapes)');

            // Statut normalisé avec FK
            $table->unsignedBigInteger('current_status_id')
                  ->nullable()
                  ->after('current_etape_id')
                  ->comment('Statut courant (FK statuses) — remplace status INT)');

            // Données structurées par étape
            $table->json('step_data')
                  ->nullable()
                  ->after('step_contents')
                  ->comment('Données formulaire par étape {"depot":{...},"visite":{...}}');

            // Date de passage à l'étape courante (pour calcul SLA)
            $table->timestamp('etape_started_at')
                  ->nullable()
                  ->after('step_data')
                  ->comment('Date d\'entrée dans l\'étape courante (calcul SLA)');

            // Clés étrangères
            $table->foreign('current_etape_id')
                  ->references('id')->on('etapes')
                  ->nullOnDelete();

            $table->foreign('current_status_id')
                  ->references('id')->on('statuses')
                  ->restrictOnDelete();

            // Index pour les requêtes fréquentes du BackOffice
            $table->index(['prestation_id', 'current_etape_id', 'current_status_id'],
                          'idx_requete_workflow_state');
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropForeign(['current_etape_id']);
            $table->dropForeign(['current_status_id']);
            $table->dropIndex('idx_requete_workflow_state');
            $table->dropColumn([
                'current_etape_id',
                'current_status_id',
                'step_data',
                'etape_started_at',
            ]);
        });

        \DB::statement('ALTER TABLE requetes ENGINE=MyISAM');
    }
};
