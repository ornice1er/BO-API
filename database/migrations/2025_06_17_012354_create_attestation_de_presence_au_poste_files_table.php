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
        Schema::create('attestations_de_presence_au_poste_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('filename')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('adpap_id')->nullable(); // Clé étrangère vers une table à préciser
            $table->timestamps();

            // Relation : attention à remplacer par le nom correct de la table si différent
            $table->foreign('adpap_id')->references('id')->on('attestation_de_presence_au_postes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestations_de_presence_au_poste_files');
    }
};
