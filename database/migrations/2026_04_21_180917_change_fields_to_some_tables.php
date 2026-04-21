<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
                $table->enum('type', ['depot', 'traitement', 'visite','commission', 'delivrance'])
                  ->default('traitement')
                  ->after('name')
                  ->comment('Phase métier de l\'étape')
                  ->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('some_tables', function (Blueprint $table) {
            //
        });
    }
};
