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
        Schema::create('villages', function (Blueprint $table) {
            $table->id(); // Colonne ID auto-incrémentée
            $table->string('name'); // Nom du village
            $table->string('code')->unique(); // Code unique pour le village
            $table->foreignId('district_id')->constrained()->onDelete('cascade'); // Clé étrangère vers le district
            $table->boolean('is_active')->default(true); // État d'activation
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
