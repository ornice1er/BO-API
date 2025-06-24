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
        Schema::create('start_points', function (Blueprint $table) {
            $table->id();
              $table->unsignedBigInteger('prestation_id');
            $table->unsignedBigInteger('unite_admin_id');
            $table->timestamps();

            // Clés étrangères
            $table->foreign('prestation_id')->references('id')->on('prestations')->onDelete('cascade');
            $table->foreign('unite_admin_id')->references('id')->on('unite_admins')->onDelete('cascade');

            // Index pour optimiser les requêtes
            $table->index(['prestation_id', 'unite_admin_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('start_points');
    }
};
