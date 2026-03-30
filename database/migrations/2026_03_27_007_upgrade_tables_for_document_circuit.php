<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 7/11 — Enrichissement des tables existantes pour le circuit documentaire
 *
 * Trois tables reçoivent des colonnes supplémentaires issues de l'analyse du flux 2
 * (agrément). Ces ajouts sont rétrocompatibles : les valeurs par défaut garantissent
 * que le flux 1 (habilitation) continue de fonctionner sans modification.
 *
 * ETAPES :
 *   produces_document      → vrai si cette étape génère un artefact système
 *                            (projet de lettre, décision d'agrément...)
 *   document_template_key  → clé du template Blade/PDF à utiliser pour la génération
 *                            (ex: 'documents.projet_lettre_agrement')
 *   visibility_scope       → qui peut voir les demandes à cette étape par défaut
 *                            (complète etape_visibilites pour les cas simples)
 *
 * WORKFLOW_TRANSITIONS :
 *   condition_type         → enum élargi : + 'paraphe' + 'prevalidation'
 *     paraphe              : un acteur (DGT) appose son paraphe numérique sur un doc
 *     prevalidation        : un acteur (DNSP) pré-valide avant signature finale
 *
 * PRESTATIONS :
 *   has_document_circuit   → active le sous-système document_circuit_etapes
 *                            pour cette prestation (false par défaut = flux 1)
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- etapes ---
        Schema::table('etapes', function (Blueprint $table) {
            $table->boolean('produces_document')
                  ->default(false)
                  ->after('sla_days')
                  ->comment('Vrai si l\'étape génère un document système (lettre, décision...)');

            $table->string('document_template_key')
                  ->nullable()
                  ->after('produces_document')
                  ->comment('Clé template génération doc (ex: documents.projet_lettre)');
        });

        // --- workflow_transitions : modifier l'enum condition_type ---
        // MySQL ne supporte pas ALTER COLUMN sur un ENUM directement via Blueprint,
        // on passe par une instruction brute.
        \DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM(
                'auto',
                'validation',
                'rejet',
                'complement',
                'signature',
                'cloture',
                'paraphe',
                'prevalidation'
            ) NOT NULL DEFAULT 'auto'
            COMMENT 'Événement déclencheur — paraphe et prevalidation ajoutés pour flux 2'
        ");

        // --- prestations ---
        Schema::table('prestations', function (Blueprint $table) {
            $table->boolean('has_document_circuit')
                  ->default(false)
                  ->after('is_automatic_delivered')
                  ->comment('Active le circuit de signature séquentiel sur documents produits');
        });
    }

    public function down(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            $table->dropColumn(['produces_document', 'document_template_key']);
        });

        \DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM('auto','validation','rejet','complement','signature','cloture')
            NOT NULL DEFAULT 'auto'
        ");

        Schema::table('prestations', function (Blueprint $table) {
            $table->dropColumn('has_document_circuit');
        });
    }
};
