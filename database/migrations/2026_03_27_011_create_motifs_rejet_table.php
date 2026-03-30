<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 11/11 — Création de `motifs_rejet`
 *
 * Le flux 2 (FA2 et FA4) introduit un mécanisme de rejet structuré :
 * l'agent choisit dans une LISTE de motifs prédéfinis, puis peut ajouter
 * un détail textuel libre (FA2.5 : "L'agent peut choisir dans la liste
 * de motif de rejet... L'agent peut donner des détails dans un champ textuel").
 *
 * Ce comportement est différent du rejet simple du flux 1 (motif libre uniquement).
 *
 * Cette table configure les motifs disponibles PAR PRESTATION et PAR ÉTAPE
 * (les motifs valables lors d'un rejet DSSMST diffèrent de ceux valables
 * lors d'un rejet DNSP).
 *
 * Usages couverts :
 *   FA2 (DSSMST) : pièces manquantes, champ mal renseigné, demande incomplète
 *   FA4 (DNSP)   : demande non conforme, informations incorrectes, non éligible
 *
 * Le motif sélectionné est ensuite stocké dans :
 *   - requete_etape_logs.metadata  pour les rejets de workflow
 *   - document_acte_logs.comment   pour les rejets de circuit document
 *
 * is_final : si vrai, ce motif entraîne un rejet définitif (pas de complément possible)
 *            → correspond au statut 'rejeté-clos' vs 'rejeté' du flux 1
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motifs_rejet', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prestation_id')
                  ->nullable()
                  ->comment('NULL = motif global applicable à toutes les prestations');

            $table->unsignedBigInteger('etape_id')
                  ->nullable()
                  ->comment('NULL = motif applicable à toutes les étapes de rejet');

            $table->string('code', 50)
                  ->comment('Code court (ex: PIECES_MANQUANTES, NON_CONFORME)');

            $table->string('libelle')
                  ->comment('Libellé affiché à l\'agent dans la liste déroulante');

            $table->text('description')
                  ->nullable()
                  ->comment('Explication interne du motif (non affichée au requérant)');

            $table->boolean('allow_complement')
                  ->default(true)
                  ->comment('Vrai = le requérant peut soumettre un complément après ce rejet');

            $table->boolean('is_final')
                  ->default(false)
                  ->comment('Vrai = rejet définitif, pas de recours (rejeté-clos)');

            $table->boolean('is_active')
                  ->default(true);

            $table->unsignedInteger('order')
                  ->default(0)
                  ->comment('Ordre d\'affichage dans la liste');

            $table->timestamps();

            $table->unique(['prestation_id', 'etape_id', 'code'], 'uq_motif_code');
            $table->index(['prestation_id', 'etape_id', 'is_active'], 'idx_motif_lookup');

            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->cascadeOnDelete();

            $table->foreign('etape_id')
                  ->references('id')->on('etapes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motifs_rejet');
    }
};
