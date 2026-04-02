<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 17 — Liaison workflow_transitions ↔ prestation_statuses
 *
 * CONTEXTE
 * ─────────
 * La table `prestation_statuses` définit quels statuts sont autorisés
 * pour chaque prestation. C'est un référentiel de cohérence métier.
 *
 * Avant cette migration :
 *   - workflow_transitions.status_result_id → FK vers statuses(id)
 *     → N'importe quel statut global peut être assigné à n'importe
 *       quelle transition, même s'il n'est pas pertinent pour la prestation.
 *
 * Après cette migration :
 *   - Ajout de la colonne prestation_status_id → FK vers prestation_statuses(id)
 *     → Le statut résultant d'une transition est validé via le couple
 *       (prestation_id, status_id) — exactement ce que prestation_statuses modélise.
 *
 * STRATÉGIE ADOPTÉE
 * ──────────────────
 * On NE remplace PAS status_result_id (FK directe vers statuses) —
 * elle reste utile pour les requêtes rapides sans jointure.
 * On AJOUTE prestation_status_id comme colonne de validation stricte.
 *
 * Le moteur de workflow peut ainsi :
 *   1. Lire status_result_id pour poster le statut rapidement
 *   2. Valider via prestation_status_id que ce statut est autorisé
 *      pour cette prestation avant de l'appliquer
 *
 * En production, une contrainte CHECK (commentée ci-dessous) pourrait
 * garantir la cohérence au niveau MySQL 8.0+. On la laisse en commentaire
 * car elle nécessite que prestation_statuses soit toujours à jour en premier.
 *
 * MIGRATION DES DONNÉES EXISTANTES
 * ──────────────────────────────────
 * Le script de migration remplit automatiquement prestation_status_id
 * pour les transitions existantes en faisant le matching
 * (prestation_id + status_result_id) → prestation_statuses.id
 * Les transitions sans correspondance reçoivent NULL (nullable).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ajouter les index manquants sur prestation_statuses ───────────
        // La table n'a que PRIMARY KEY — on ajoute les index utiles
        Schema::table('prestation_statuses', function (Blueprint $table) {
            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->cascadeOnDelete();

            $table->foreign('status_id')
                  ->references('id')->on('statuses')
                  ->cascadeOnDelete();

            // Index composite pour la vérification rapide (prestation_id, status_id)
            $table->unique(['prestation_id', 'status_id'], 'uq_prestation_status');

            // Index pour le filtre UI (tous les statuts d'une prestation)
            $table->index('prestation_id', 'idx_ps_prestation');
        });

        // ── 2. Ajouter prestation_status_id sur workflow_transitions ─────────
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->unsignedBigInteger('prestation_status_id')
                  ->nullable()
                  ->after('status_result_id')
                  ->comment(
                      'FK vers prestation_statuses — valide que le statut résultant ' .
                      'est autorisé pour cette prestation. ' .
                      'NULL = transition existante non encore validée.'
                  );

            $table->foreign('prestation_status_id')
                  ->references('id')
                  ->on('prestation_statuses')
                  ->nullOnDelete();

            $table->index('prestation_status_id', 'idx_wt_prestation_status');
        });

        // ── 3. Remplir prestation_status_id pour les transitions existantes ──
        \DB::statement("
            UPDATE workflow_transitions wt
            JOIN prestation_statuses ps
                ON ps.prestation_id = wt.prestation_id
               AND ps.status_id     = wt.status_result_id
            SET wt.prestation_status_id = ps.id
            WHERE wt.prestation_status_id IS NULL
        ");

        $updated = \DB::table('workflow_transitions')
            ->whereNotNull('prestation_status_id')
            ->count();

        $missing = \DB::table('workflow_transitions')
            ->whereNull('prestation_status_id')
            ->count();

        if ($missing > 0) {
            \Log::warning(
                "Migration 017 : {$missing} transition(s) ont prestation_status_id = NULL. " .
                "Vérifier que prestation_statuses est correctement peuplé pour ces prestations."
            );
        }
    }

    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropForeign(['prestation_status_id']);
            $table->dropIndex('idx_wt_prestation_status');
            $table->dropColumn('prestation_status_id');
        });

        Schema::table('prestation_statuses', function (Blueprint $table) {
            $table->dropForeign(['prestation_id']);
            $table->dropForeign(['status_id']);
            $table->dropUnique('uq_prestation_status');
            $table->dropIndex('idx_ps_prestation');
        });
    }
};
