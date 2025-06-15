<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promoters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du promoteur
            $table->string('email')->unique(); // Email unique
            $table->string('phone')->nullable(); // Numéro de téléphone (optionnel)
            $table->enum('status', ['active', 'inactive'])->default('active'); // Statut du promoteur (actif ou inactif)
            $table->timestamps(); // Pour les dates de création et de mise à jour
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promoters');
    }
}
