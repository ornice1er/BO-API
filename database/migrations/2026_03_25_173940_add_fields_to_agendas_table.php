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
        Schema::table('agendas', function (Blueprint $table) {
            $table->enum('from',['Usager','Métier'])->nullable();
            $table->enum('status',['Ouvert','Clos'])->change();
            $table->enum('priority',['Faible','Moyenne','Haute'])->change();
            $table->foreignId('requete_id')->nullable();
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
