<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier l'enum pour ajouter certificat et agrement
        DB::statement("ALTER TABLE etape_document_produits MODIFY COLUMN type ENUM('lettre','decision','attestation','pv','certificat','agrement') NOT NULL DEFAULT 'lettre'");

        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->boolean('avancer_workflow')
                  ->default(false)
                  ->after('condition_type')
                  ->comment('Si activé, avance automatiquement le workflow après récupération/génération du document');
        });
    }

    public function down(): void
    {
        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->dropColumn('avancer_workflow');
        });
        DB::statement("ALTER TABLE etape_document_produits MODIFY COLUMN type ENUM('lettre','decision','attestation','pv') NOT NULL DEFAULT 'lettre'");
    }
};
