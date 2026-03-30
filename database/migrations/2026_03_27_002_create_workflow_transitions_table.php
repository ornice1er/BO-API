<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 2/6 — Création de la table `workflow_transitions`
 *
 * Remplace la colonne `workflows.next_etapes` (varchar sérialisé, non normalisé)
 * par une table relationnelle propre.
 *
 * Chaque ligne représente une transition possible POUR UNE PRESTATION DONNÉE :
 *   prestation X : étape A --[condition]--> étape B  =>  statut S posé sur la requête
 *
 * condition_type couvre tous les cas du flux documenté :
 *   - auto        : transition automatique dès que l'étape source est terminée
 *   - validation  : agent valide explicitement (bouton "Valider")
 *   - rejet       : agent rejette (dossier incomplet, FA1)
 *   - complement  : requérant soumet un complément suite à rejet (FA1.3→FA1.9)
 *   - signature   : ministre appose sa signature numérique (FN31→FN32)
 *   - cloture     : clôture définitive sans recours possible (rejeté-clos, FA2 comité)
 *
 * is_active permet de désactiver une transition sans la supprimer
 * (utile pour tester une nouvelle configuration sans impacter la prod).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prestation_id')
                  ->comment('Prestation à laquelle s\'applique cette transition');

            $table->unsignedBigInteger('etape_from_id')
                  ->comment('Étape source (null = point d\'entrée, première étape)');

            $table->unsignedBigInteger('etape_to_id')
                  ->nullable()
                  ->comment('Étape cible (null = étape terminale, fin du workflow)');

            $table->enum('condition_type', [
                    'auto',
                    'validation',
                    'rejet',
                    'complement',
                    'signature',
                    'cloture',
                ])->default('auto')
                  ->comment('Événement déclencheur de la transition');

            $table->unsignedBigInteger('status_result_id')
                  ->comment('Statut posé sur la requête après cette transition');

            $table->boolean('notify_requérant')
                  ->default(false)
                  ->comment('Déclenche une notification vers le requérant');

            $table->boolean('notify_agent')
                  ->default(false)
                  ->comment('Déclenche une notification vers l\'agent destinataire');

            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Permet de désactiver une transition sans la supprimer');

            $table->unsignedInteger('order')
                  ->default(0)
                  ->comment('Ordre d\'évaluation si plusieurs transitions partent de la même étape');

            $table->timestamps();

            // Index
            $table->index(['prestation_id', 'etape_from_id', 'condition_type'],
                          'idx_transition_lookup');

            // Clés étrangères
            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->cascadeOnDelete();

            $table->foreign('etape_from_id')
                  ->references('id')->on('etapes')
                  ->cascadeOnDelete();

            $table->foreign('etape_to_id')
                  ->references('id')->on('etapes')
                  ->nullOnDelete();

            $table->foreign('status_result_id')
                  ->references('id')->on('statuses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_transitions');
    }
};
