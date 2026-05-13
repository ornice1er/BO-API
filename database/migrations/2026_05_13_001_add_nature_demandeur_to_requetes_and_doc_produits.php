<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gestion du document produit selon la nature du demandeur.
 *
 * 1. requetes.request_type
 *    Capturé depuis meta.requestType envoyé par le portail lors du dépôt.
 *    Exemples de valeurs : "physique", "morale", "entreprise", "association"…
 *    null = non renseigné par le portail (pas de différenciation).
 *
 * 2. etape_document_produits.demandeur_type
 *    null  = applicable à tous (comportement actuel inchangé)
 *    sinon = valeur exacte à matcher avec requetes.request_type
 *
 *    Lors du getDocProduit, priorité au template qui matche request_type,
 *    fallback sur demandeur_type = null si aucun match trouvé.
 *
 * Contrainte unique mise à jour :
 *    (prestation_id, slug, demandeur_type) au lieu de (prestation_id, slug)
 *    → permet deux lignes pour le même slug selon le type de demandeur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->string('request_type')
                  ->nullable()
                  ->after('current_status_id')
                  ->comment('Valeur de meta.requestType transmise par le portail');
        });

        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->string('demandeur_type')
                  ->nullable()
                  ->default(null)
                  ->after('order')
                  ->comment('null = tous, sinon doit matcher request_type de la requête');

            $table->dropUnique('uq_doc_produit_slug');
            $table->unique(['prestation_id', 'slug', 'demandeur_type'], 'uq_doc_produit_slug_type');
        });
    }

    public function down(): void
    {
        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->dropUnique('uq_doc_produit_slug_type');
            $table->unique(['prestation_id', 'slug'], 'uq_doc_produit_slug');
            $table->dropColumn('demandeur_type');
        });

        Schema::table('requetes', function (Blueprint $table) {
            $table->dropColumn('request_type');
        });
    }
};
