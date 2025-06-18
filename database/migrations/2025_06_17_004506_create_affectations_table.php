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
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->boolean('isLast')->default(0);
            $table->unsignedBigInteger('unite_admin_up');
            $table->unsignedBigInteger('unite_admin_down');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->tinyInteger('sens')->default(1); // direction : 1=vers le bas, 2=vers le haut
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->text('instruction')->nullable();
            $table->integer('delay')->nullable();

            // Clés étrangères (à adapter selon ton schéma)
            // $table->foreign('unite_admin_up')->references('id')->on('unite_admins');
            // $table->foreign('unite_admin_down')->references('id')->on('unite_admins');
            // $table->foreign('requete_id')->references('id')->on('requetes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
