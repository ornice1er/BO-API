<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affectation de stage & destinataire des documents produits.
 *
 * - requetes.structure_id : unité administrative (structure d'accueil) à laquelle
 *   le demandeur est affecté pour son stage. Renseignée par l'agent au traitement.
 *
 * - etape_document_produits.destinataire : à qui part le document une fois finalisé.
 *     'usager'    → le demandeur (délivrance PNS, comportement actuel) — défaut
 *     'structure' → la structure d'accueil, par e-mail avec le PDF en pièce jointe
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (!Schema::hasColumn('requetes', 'structure_id')) {
                $table->foreignId('structure_id')->nullable()->after('project_id');
            }
        });

        Schema::table('etape_document_produits', function (Blueprint $table) {
            if (!Schema::hasColumn('etape_document_produits', 'destinataire')) {
                $table->enum('destinataire', ['usager', 'structure'])
                    ->default('usager')
                    ->after('demandeur_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (Schema::hasColumn('requetes', 'structure_id')) {
                $table->dropColumn('structure_id');
            }
        });

        Schema::table('etape_document_produits', function (Blueprint $table) {
            if (Schema::hasColumn('etape_document_produits', 'destinataire')) {
                $table->dropColumn('destinataire');
            }
        });
    }
};
