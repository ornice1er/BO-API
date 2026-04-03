<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 10/11 — Création de `etape_visibilites` et `document_acte_logs`
 *
 * ETAPE_VISIBILITES — Normalisation des banettes
 * -----------------------------------------------
 * Remplace `etape_prestation_statuses.banettes` (varchar sérialisé).
 *
 * Le flux 2 rend ce besoin explicite : après chaque action sur un document,
 * ce dernier "devient accessible par X". Ce n'est pas juste un statut —
 * c'est une règle de visibilité par rôle, par transition.
 *
 * Exemples tirés du flux agrément :
 *
 *   Après FN15 (statut "envoyé") :
 *     → DGT     : can_read=true, can_act=true   (peut parapher)
 *     → DSSMST  : can_read=true, can_act=false  (lecture seule)
 *
 *   Après FN22 (DGT paraphe) :
 *     → Ministre           : can_read=true, can_act=true  (peut signer)
 *     → Secrétariat_min    : can_read=true, can_act=false
 *     → DGT                : can_read=true, can_act=false (lecture seule)
 *
 *   Après FN31 (Ministre signe lettre) :
 *     → DNSP    : can_read=true, can_act=true   (peut pré-valider)
 *     → DSSMST  : can_read=true, can_act=false
 *
 * scope_type distingue deux niveaux de visibilité :
 *   - requete   : visibilité sur la fiche demande dans le BackOffice
 *   - document  : visibilité sur un document produit spécifique
 *                 (doc_produit_id doit alors être renseigné)
 *
 * DOCUMENT_ACTE_LOGS — Journal immuable des actions sur documents
 * ----------------------------------------------------------------
 * Complète requete_etape_logs (qui trace les transitions de workflow)
 * avec le détail des actions sur chaque document produit.
 *
 * Enregistre : qui a paraphé/signé/corrigé quoi, quand, avec quel fichier.
 * Immuable : pas d'updated_at, aucune modification après insertion.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- VISIBILITÉS PAR RÔLE ---
        Schema::create('etape_visibilites', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('workflow_transition_id')
                  ->comment('Transition après laquelle cette visibilité s\'applique');

            $table->string('role_name', 100)
                  ->comment('Nom du rôle Spatie concerné (ex: DGT, DNSP, Ministre)');

            $table->boolean('can_read')
                  ->default(true)
                  ->comment('Le rôle peut consulter la demande/document');

            $table->boolean('can_act')
                  ->default(false)
                  ->comment('Le rôle peut agir (parapher, signer, valider...)');

            $table->enum('scope_type', ['requete', 'document'])
                  ->default('requete')
                  ->comment('Porte sur la requête ou sur un document produit spécifique');

            $table->unsignedBigInteger('doc_produit_id')
                  ->nullable()
                  ->comment('Si scope=document, quel type de document est concerné');

            $table->timestamps();

            $table->index(['workflow_transition_id', 'role_name'], 'idx_visibilite_role');

            $table->foreign('workflow_transition_id')
                  ->references('id')->on('workflow_transitions')
                  ->cascadeOnDelete();

            $table->foreign('doc_produit_id')
                  ->references('id')->on('etape_document_produits')
                  ->nullOnDelete();
        });

        // --- JOURNAL DES ACTIONS SUR DOCUMENTS ---
        Schema::create('document_acte_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('document_acte_id')
                  ->comment('Document concerné');

            $table->unsignedBigInteger('circuit_step_id')
                  ->comment('Étape du circuit franchie');

            $table->enum('action', [
                    'edition',
                    'correction',
                    'paraphe',
                    'prevalidation',
                    'signature',
                    'rejet',
                ])->comment('Action effectuée sur le document');

            $table->unsignedBigInteger('triggered_by')
                  ->nullable()
                  ->comment('ID user ayant effectué l\'action');

            $table->string('role_name', 100)
                  ->nullable()
                  ->comment('Rôle de l\'acteur au moment de l\'action');

            $table->string('file_path_before')
                  ->nullable()
                  ->comment('Chemin fichier avant modification (FA3 corrections)');

            $table->string('file_path_after')
                  ->nullable()
                  ->comment('Chemin fichier après action (avec paraphe/signature apposés)');

            $table->text('comment')
                  ->nullable()
                  ->comment('Motif de rejet ou note libre');

            $table->json('metadata')
                  ->nullable()
                  ->comment('Données contextuelles {"ref_signature":..., "coordonnees":...}');

            $table->timestamp('acted_at')
                  ->useCurrent()
                  ->comment('Horodatage exact de l\'action');

            // Immuable — pas d'updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index('document_acte_id', 'idx_acte_log_doc');
            $table->index(['document_acte_id', 'acted_at'], 'idx_acte_log_timeline');

            $table->foreign('document_acte_id')
                  ->references('id')->on('document_actes')
                  ->cascadeOnDelete();

            $table->foreign('circuit_step_id')
                  ->references('id')->on('document_circuit_etapes')
                  ->restrictOnDelete();

            $table->foreign('triggered_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_acte_logs');
        Schema::dropIfExists('etape_visibilites');
    }
};
