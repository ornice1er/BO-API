<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 3/6 — Création de la table `etape_documents`
 *
 * Remplace la table générique `files` (liée uniquement à `prestation_id`)
 * par une configuration fine des pièces justificatives requises
 * PAR ÉTAPE et PAR PRESTATION.
 *
 * Exemple pour la prestation "Habilitation centre de formation" :
 *   - étape "dépôt"        : statuts juridiques, RCCM, plan de localisation...
 *   - étape "visite site"  : rapport de visite, photos...
 *   - étape "commission"   : synthèse d'étude du dossier...
 *
 * accepted_mime_types : JSON ["application/pdf","image/jpeg"]
 *   → validation côté Laravel avant upload (Rule::file()->types([...]))
 *
 * max_size_kb : taille max en kilo-octets (ex: 2048 = 2 Mo)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etape_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prestation_id');
            $table->unsignedBigInteger('etape_id');

            $table->string('name')
                  ->comment('Libellé affiché au requérant (ex: "Statuts de l\'établissement")');

            $table->string('slug')
                  ->comment('Identifiant technique (ex: statuts_etablissement)');

            $table->boolean('is_required')
                  ->default(true)
                  ->comment('Pièce obligatoire ou facultative');

            $table->json('accepted_mime_types')
                  ->nullable()
                  ->comment('Types MIME acceptés ["application/pdf","image/jpeg"]');

            $table->unsignedInteger('max_size_kb')
                  ->nullable()
                  ->comment('Taille maximale du fichier en Ko');

            $table->string('description')->nullable()
                  ->comment('Aide contextuelle affichée sous le champ upload');

            $table->unsignedInteger('order')
                  ->default(0)
                  ->comment('Ordre d\'affichage dans le formulaire');

            $table->timestamps();

            $table->unique(['prestation_id', 'etape_id', 'slug'], 'uq_etape_doc_slug');

            $table->index(['prestation_id', 'etape_id'], 'idx_etape_doc_lookup');

            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->cascadeOnDelete();

            $table->foreign('etape_id')
                  ->references('id')->on('etapes')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etape_documents');
    }
};
