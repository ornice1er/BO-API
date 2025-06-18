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
        Schema::create('agrement_medecins', function (Blueprint $table) {
            $table->id();
            $table->uuid('code');
            $table->string('ifu')->nullable();
            $table->string('identity');
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->timestamps();

            // Clé étrangère facultative
            $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agrement_medecins');
    }
};
