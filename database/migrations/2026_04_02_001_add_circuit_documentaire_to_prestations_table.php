<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajout du champ `has_document_circuit` à la table `prestations`.
 *
 * Ce booléen active le sous-système de signatures :
 * quand il est vrai, tout livrable généré par cette prestation
 * suit un circuit de validation/signature avant délivrance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestations', function (Blueprint $table) {
            $table->boolean('has_document_circuit')
                  ->default(false)
                  ->after('needOut')
                  ->comment('Active le circuit de signatures sur les livrables de cette prestation');
        });
    }

    public function down(): void
    {
        Schema::table('prestations', function (Blueprint $table) {
            $table->dropColumn('has_document_circuit');
        });
    }
};
