<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restauration des colonnes legacy supprimées par 2026_05_21_001_cleanup_legacy_columns.
 *
 * Raison : le cleanup a été appliqué sur la base avant que le code nettoyé
 * ne soit déployé. Le code en production écrit encore certaines de ces colonnes
 * (ex. `pris_en_charge`), provoquant « Unknown column ... ».
 *
 * Cette migration rétablit la parité schéma ↔ code déployé. Une fois le code
 * nettoyé déployé, le cleanup pourra être ré-appliqué.
 * Idempotente (Schema::hasColumn) pour pouvoir tourner sans risque.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (!Schema::hasColumn('requetes', 'isAutorized'))        $table->boolean('isAutorized')->default(false);
            if (!Schema::hasColumn('requetes', 'finaleResponse'))     $table->text('finaleResponse')->nullable();
            if (!Schema::hasColumn('requetes', 'attach'))             $table->text('attach')->nullable();
            if (!Schema::hasColumn('requetes', 'content2'))           $table->longText('content2')->nullable();
            if (!Schema::hasColumn('requetes', 'content3'))           $table->longText('content3')->nullable();
            if (!Schema::hasColumn('requetes', 'eps_id'))             $table->unsignedBigInteger('eps_id')->nullable();
            if (!Schema::hasColumn('requetes', 'pris_en_charge'))     $table->boolean('pris_en_charge')->default(false);
            if (!Schema::hasColumn('requetes', 'needCorrection'))     $table->boolean('needCorrection')->default(false);
            if (!Schema::hasColumn('requetes', 'hasReachedAgreement')) $table->boolean('hasReachedAgreement')->default(false);
        });

        Schema::table('etapes', function (Blueprint $table) {
            if (!Schema::hasColumn('etapes', 'produces_document'))      $table->boolean('produces_document')->default(false);
            if (!Schema::hasColumn('etapes', 'document_template_key'))  $table->string('document_template_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropColumn([
                'isAutorized', 'finaleResponse', 'attach', 'content2', 'content3',
                'eps_id', 'pris_en_charge', 'needCorrection', 'hasReachedAgreement',
            ]);
        });

        Schema::table('etapes', function (Blueprint $table) {
            $table->dropColumn(['produces_document', 'document_template_key']);
        });
    }
};
