<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 8/11 — Création de `etape_document_produits`
 *
 * DISTINCTION FONDAMENTALE avec `etape_documents` (migration 003) :
 *
 *   etape_documents          → pièces FOURNIES par le requérant (upload)
 *                              ex: RCCM, statuts, plan de localisation
 *
 *   etape_document_produits → documents GÉNÉRÉS par le système à une étape
 *                              ex: projet de lettre, décision d'agrément
 *                              Ce sont des artefacts BackOffice, pas des uploads.
 *
 * Chaque ligne configure UN TYPE de document produit pour UNE PRESTATION.
 * Les instances réelles (par requête) sont dans `document_actes` (migration 009).
 *
 * Champs clés :
 *
 *   type          : catégorie du document
 *                   - lettre      : courrier administratif (projet de lettre FN7→FN15)
 *                   - decision    : acte décisionnel (décision d'agrément FN55→FN57)
 *                   - attestation : certificat remis au requérant
 *                   - pv          : procès-verbal (PV de visite de site)
 *
 *   numero_prefix : préfixe du numéro d'identification automatique
 *                   ex: 'PL' → PL-2026-00042
 *
 *   template_key  : clé du template Blade/PDF utilisé pour la génération
 *                   ex: 'documents.agrement.projet_lettre'
 *
 *   etape_edition_id   : étape où l'agent édite/génère le document (DSSMST FN7)
 *   etape_delivrance_id: étape où le document est remis au requérant (après signature)
 *
 *   allow_correction : si vrai, le document peut être corrigé après génération
 *                      mais avant la première action du circuit (FA3)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etape_document_produits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prestation_id');

            $table->string('name')
                  ->comment('Libellé métier (ex: "Projet de lettre d\'agrément")');

            $table->string('slug')
                  ->comment('Identifiant technique (ex: projet_lettre_agrement)');

            $table->enum('type', ['lettre', 'decision', 'attestation', 'pv'])
                  ->default('lettre')
                  ->comment('Catégorie du document produit');

            $table->string('numero_prefix', 10)
                  ->default('DOC')
                  ->comment('Préfixe numérotation auto (ex: PL → PL-2026-00042)');

            $table->string('template_key')
                  ->comment('Clé template génération (ex: documents.agrement.projet_lettre)');

            $table->unsignedBigInteger('etape_edition_id')
                  ->comment('Étape où le document est édité/généré');

            $table->unsignedBigInteger('etape_delivrance_id')
                  ->nullable()
                  ->comment('Étape où le doc est remis au requérant (après circuit complet)');

            $table->boolean('allow_correction')
                  ->default(true)
                  ->comment('FA3 : correction possible avant entrée dans le circuit signature');

            $table->unsignedInteger('order')
                  ->default(1)
                  ->comment('Ordre de traitement si plusieurs docs produits dans la prestation');

            $table->timestamps();

            $table->unique(['prestation_id', 'slug'], 'uq_doc_produit_slug');
            $table->index(['prestation_id', 'etape_edition_id'], 'idx_doc_produit_edition');

            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->cascadeOnDelete();

            $table->foreign('etape_edition_id')
                  ->references('id')->on('etapes')
                  ->restrictOnDelete();

            $table->foreign('etape_delivrance_id')
                  ->references('id')->on('etapes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etape_document_produits');
    }
};
