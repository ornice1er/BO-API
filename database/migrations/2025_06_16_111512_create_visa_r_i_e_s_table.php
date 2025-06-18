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
        Schema::create('visa_r_i_e_s', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('name_structure');
            $table->string('rccm')->nullable();
            $table->string('ifu')->nullable();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('requete_id');
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index('code');
            $table->index('requete_id');

            // Clé étrangère si la table requetes existe
            // $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_r_i_e_s');
    }
};
