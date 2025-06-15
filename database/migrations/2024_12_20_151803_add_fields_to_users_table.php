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
        Schema::table('users', function (Blueprint $table) {

            $table->string('spoken_languages')->nullable();
            $table->string('understood_languages')->nullable();
            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->unsignedBigInteger('statut_agent_id')->nullable();
            $table->unsignedBigInteger(column: 'candidat_id')->nullable();
            $table->foreign('municipality_id')->references('id')->on('municipalities'); // Clé étrangère vers le département

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
