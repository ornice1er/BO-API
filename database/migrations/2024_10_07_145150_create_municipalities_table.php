<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id(); // Colonne ID
            $table->string('name'); // Nom de la municipalité
            $table->string('code')->unique(); // Code unique
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade'); // Clé étrangère vers le département
            $table->boolean('is_active')->default(true); // État d'activation
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
