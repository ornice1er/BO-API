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
        Schema::create('agrement_medecins_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('filename');
            $table->string('reference')->nullable();
            $table->string('vague');
            $table->unsignedBigInteger('am_id');
            $table->timestamps();

            // Clé étrangère vers la table des médecins agréés (ex. "agrement_medecins")
            $table->foreign('am_id')->references('id')->on('agrement_medecins')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agrement_medecins_files');
    }
};
