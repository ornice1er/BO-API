<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 9/11 — Création de `document_circuit_etapes` et `document_actes`
 *
 * Ces deux tables forment le cœur du sous-système de signature séquentielle
 * identifié dans le flux 2 (agrément).
 *
 * ┌─────────────────────────────────────────────────────────────┐
 * │  document_circuit_etapes  (CONFIGURATION)                   │
 * │  "Pour ce type de doc produit, qui fait quoi dans quel ordre"│
 * ├─────────────────────────────────────────────────────────────┤
 * │  Flux agrément — Projet de lettre :                         │
 * │    order 1 → DSSMST   → edition      (génère le doc)        │
 * │    order 2 → DGT      → paraphe      (FN21)                 │
 * │    order 3 → Ministre → signature    (FN30)                 │
 * │    order 4 → DNSP     → prevalidation(FN42)                 │
 * │                                                             │
 * │  Flux agrément — Décision d'agrément :                      │
 * │    order 1 → DSSMST   → edition      (FN52)                 │
 * │    order 2 → DGT      → paraphe      (FN64)                 │
 * │    order 3 → Ministre → signature    (FN84)                 │
 * └─────────────────────────────────────────────────────────────┘
 *
 * ┌─────────────────────────────────────────────────────────────┐
 * │  document_actes  (INSTANCES RÉELLES par requête)            │
 * │  "Le document concret généré pour la demande #456"          │
 * ├─────────────────────────────────────────────────────────────┤
 * │  - Numéro d'identification attribué automatiquement         │
 * │  - Chemin fichier PDF généré                                │
 * │  - Étape courante dans le circuit (pointeur)                │
 * │  - Statut : en_edition | en_circuit | complete | rejete     │
 * └─────────────────────────────────────────────────────────────┘
 *
 * IMPORTANT : document_circuit_etapes.action_type utilise les mêmes
 * valeurs que workflow_transitions.condition_type pour cohérence,
 * plus 'edition' (spécifique aux docs produits).
 *
 * is_blocking : si vrai, la requête ne peut pas avancer dans son workflow
 * tant que cette étape du circuit document n'est pas franchie.
 * Exemple : la signature du ministre est bloquante — la demande ne passe
 * pas au statut 'validé' tant que le document n'est pas signé.
 *
 * unite_admin_id : unité administrative responsable de cette étape du circuit.
 * Permet au BackOffice d'afficher le document dans la bonne banette.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- TABLE DE CONFIGURATION DU CIRCUIT ---
        Schema::create('document_circuit_etapes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('doc_produit_id')
                  ->comment('Type de document concerné');

            $table->unsignedBigInteger('unite_admin_id')
                  ->nullable()
                  ->comment('Unité admin responsable de cette étape du circuit');

            $table->string('role_name', 100)
                  ->comment('Rôle Spatie requis (ex: DGT, DSSMST, Ministre)');

            $table->enum('action_type', [
                    'edition',        // génération initiale du document
                    'paraphe',        // apposition paraphe numérique
                    'prevalidation',  // avis favorable avant signature finale
                    'signature',      // signature numérique officielle
                    'correction',     // FA3 : correction avant circuit
                ])->comment('Type d\'action à effectuer sur le document');

            $table->string('status_after', 100)
                  ->comment('Statut du document après cette action (ex: paraphé, signé)');

            $table->string('requete_status_after', 100)
                  ->nullable()
                  ->comment('Statut posé sur la REQUÊTE après cette action (ex: envoyé, validé)');

            $table->boolean('is_blocking')
                  ->default(true)
                  ->comment('Bloque l\'avancement du workflow requête tant que non franchi');

            $table->unsignedInteger('order')
                  ->default(1)
                  ->comment('Ordre d\'exécution dans le circuit');

            $table->timestamps();

            $table->index(['doc_produit_id', 'order'], 'idx_circuit_order');

            $table->foreign('doc_produit_id')
                  ->references('id')->on('etape_document_produits')
                  ->cascadeOnDelete();

            $table->foreign('unite_admin_id')
                  ->references('id')->on('unite_admins')
                  ->nullOnDelete();
        });

        // --- TABLE DES INSTANCES RÉELLES ---
        Schema::create('document_actes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('requete_id');

            $table->unsignedBigInteger('doc_produit_id')
                  ->comment('Configuration source (type de document)');

            $table->string('numero_identification')
                  ->unique()
                  ->comment('Numéro attribué automatiquement (ex: PL-2026-00042)');

            $table->string('file_path')
                  ->nullable()
                  ->comment('Chemin du fichier PDF généré');

            $table->unsignedBigInteger('current_circuit_step_id')
                  ->nullable()
                  ->comment('Étape courante dans le circuit (FK document_circuit_etapes)');

            $table->enum('status', [
                    'en_edition',   // en cours de saisie par l'agent DSSMST
                    'en_circuit',   // en cours de paraphe/signature
                    'complet',      // circuit entièrement parcouru
                    'rejete',       // rejeté par un acteur du circuit (FA4)
                ])->default('en_edition');

            $table->text('rejection_motif')
                  ->nullable()
                  ->comment('Motif de rejet si status = rejete (FA4 DNSP)');

            $table->timestamp('generated_at')
                  ->nullable()
                  ->comment('Date de génération du PDF');

            $table->timestamp('completed_at')
                  ->nullable()
                  ->comment('Date de complétion du circuit (dernière signature)');

            $table->timestamps();

            $table->index(['requete_id', 'doc_produit_id'], 'idx_acte_requete_doc');
            $table->index(['requete_id', 'status'], 'idx_acte_status');

            $table->foreign('requete_id')
                  ->references('id')->on('requetes')
                  ->cascadeOnDelete();

            $table->foreign('doc_produit_id')
                  ->references('id')->on('etape_document_produits')
                  ->restrictOnDelete();

            $table->foreign('current_circuit_step_id')
                  ->references('id')->on('document_circuit_etapes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_actes');
        Schema::dropIfExists('document_circuit_etapes');
    }
};
