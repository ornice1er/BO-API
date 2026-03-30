<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normaliser les valeurs existantes avant de passer aux enums NOT NULL
        DB::table('agendas')
            ->whereNotIn('status', ['Ouvert', 'Clos'])
            ->update(['status' => 'Ouvert']);

        DB::table('agendas')
            ->whereNotIn('priority', ['Faible', 'Moyenne', 'Haute'])
            ->update(['priority' => 'Faible']);

        Schema::table('agendas', function (Blueprint $table) {
            $table->enum('from', ['Usager', 'Métier'])->nullable();
            $table->enum('status', ['Ouvert', 'Clos'])->default('Ouvert')->change();
            $table->enum('priority', ['Faible', 'Moyenne', 'Haute'])->default('Faible')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            //
        });
    }
};
