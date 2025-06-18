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
        Schema::create('type_unite_admins', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('libelle', 255);
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index('code');
            $table->index('libelle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_unite_admins');
    }
};
