<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration de nettoyage — suppression des colonnes legacy
 *
 * TABLE requetes — colonnes supprimées :
 *   - isAutorized         : jamais lu, remplacé par RBAC
 *   - finaleResponse      : jamais lu, orpheline
 *   - attach              : jamais lu, fichiers gérés via requete_files
 *   - content2 / content3 : legacy storeContent(), jamais lus dans le workflow moderne
 *   - eps_id              : FK orpheline — table etape_prestation_statuses droppée (2026-04-02)
 *   - pris_en_charge      : jamais lu, info portée par RequeteEtapeLog
 *   - needCorrection      : remplacé par condition_type = 'rejet' / 'complement'
 *   - hasReachedAgreement : remplacé par condition_type = 'choix_sortie'
 *
 * TABLE etapes — colonnes supprimées :
 *   - produces_document      : remplacé par existence d'un EtapeDocumentProduit
 *   - document_template_key  : idem
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropColumn([
                'isAutorized',
                'finaleResponse',
                'attach',
                'content2',
                'content3',
                'eps_id',
                'pris_en_charge',
                'needCorrection',
                'hasReachedAgreement',
            ]);
        });

        Schema::table('etapes', function (Blueprint $table) {
            $table->dropColumn([
                'produces_document',
                'document_template_key',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->boolean('isAutorized')->default(false);
            $table->text('finaleResponse')->nullable();
            $table->text('attach')->nullable();
            $table->longText('content2')->nullable();
            $table->longText('content3')->nullable();
            $table->unsignedBigInteger('eps_id')->nullable();
            $table->boolean('pris_en_charge')->default(false);
            $table->boolean('needCorrection')->default(false);
            $table->boolean('hasReachedAgreement')->default(false);
        });

        Schema::table('etapes', function (Blueprint $table) {
            $table->boolean('produces_document')->default(false);
            $table->string('document_template_key')->nullable();
        });
    }
};
