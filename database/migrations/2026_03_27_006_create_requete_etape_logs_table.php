<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 6/6 — Création de la table `requete_etape_logs`
 *
 * Journal immuable de toutes les transitions de workflow effectuées
 * sur chaque requête. Contrairement à `parcours` (libellé libre) et
 * à `logs` (action applicative générique), cette table est structurée
 * pour le workflow : elle enregistre exactement QUELLE transition a été
 * empruntée, PAR QUI, QUAND, et avec quel commentaire.
 *
 * Cas d'usage :
 *   - Affichage du "fil d'Ariane" du dossier dans le BackOffice
 *   - Calcul des délais réels vs SLA par étape
 *   - Audit : qui a validé, qui a rejeté, quand
 *   - Reprise après sauvegarde partielle (FA2) : on sait à quelle étape
 *     le requérant s'est arrêté
 *
 * La colonne `metadata` (JSON) stocke le contexte spécifique à l'action :
 *   - pour un rejet   : {"motif": "pièce manquante", "detail": "RCCM absent"}
 *   - pour une visite : {"date_visite": "2026-04-10", "resultat": "favorable"}
 *   - pour signature  : {"date_signature": "2026-04-15", "ref_decision": "N°123"}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requete_etape_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('requete_id');

            $table->unsignedBigInteger('workflow_transition_id')
                  ->comment('Transition empruntée (lie etape_from → etape_to + condition)');

            $table->unsignedBigInteger('etape_from_id')
                  ->comment('Étape de départ (dénormalisé pour lisibilité des requêtes)');

            $table->unsignedBigInteger('etape_to_id')
                  ->nullable()
                  ->comment('Étape d\'arrivée (null si transition terminale)');

            $table->unsignedBigInteger('status_id')
                  ->comment('Statut posé sur la requête suite à cette transition');

            $table->unsignedBigInteger('triggered_by')
                  ->nullable()
                  ->comment('ID user (agent/ministre) qui a déclenché la transition');

            $table->string('triggered_by_type')
                  ->default('agent')
                  ->comment('Type d\'acteur : agent, ministre, requérant, système');

            $table->text('comment')->nullable()
                  ->comment('Commentaire libre (motif de rejet, note de l\'agent...)');

            $table->json('metadata')->nullable()
                  ->comment('Données contextuelles {"motif":..., "ref_decision":...}');

            $table->timestamp('transitioned_at')
                  ->useCurrent()
                  ->comment('Horodatage exact de la transition');

            // Pas de updated_at — ce log est immuable
            $table->timestamp('created_at')->useCurrent();

            // Index pour les requêtes fréquentes
            $table->index('requete_id', 'idx_log_requete');
            $table->index(['requete_id', 'transitioned_at'], 'idx_log_timeline');

            $table->foreign('requete_id')
                  ->references('id')->on('requetes')
                  ->cascadeOnDelete();

            $table->foreign('workflow_transition_id')
                  ->references('id')->on('workflow_transitions')
                  ->restrictOnDelete();

            $table->foreign('etape_from_id')
                  ->references('id')->on('etapes')
                  ->restrictOnDelete();

            $table->foreign('etape_to_id')
                  ->references('id')->on('etapes')
                  ->nullOnDelete();

            $table->foreign('status_id')
                  ->references('id')->on('statuses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requete_etape_logs');
    }
};
