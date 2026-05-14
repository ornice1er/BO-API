<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la colonne observation
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->text('observation')
                  ->nullable()
                  ->after('decision')
                  ->comment('Contexte ou motif libre, utile notamment pour les transitions de correction');
        });

        // Ajouter la valeur 'correction' à l'enum condition_type
        DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM('auto','validation','rejet','complement','signature','cloture','paraphe','prevalidation','choix_sortie','correction')
            NOT NULL DEFAULT 'auto'
        ");
    }

    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropColumn('observation');
        });

        DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM('auto','validation','rejet','complement','signature','cloture','paraphe','prevalidation','choix_sortie')
            NOT NULL DEFAULT 'auto'
        ");
    }
};
