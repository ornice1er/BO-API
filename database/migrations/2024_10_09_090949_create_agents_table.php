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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('numero_matricule');
            $table->unsignedBigInteger('unite_admin_id');
            $table->unsignedBigInteger('entite_admin_id');
            $table->unsignedBigInteger('fonction_agent_id');
            $table->timestamps();

            // Relations clés étrangères
            $table->foreign('unite_admin_id')->references('id')->on('unite_admins')->onDelete('cascade');
            $table->foreign('entite_admin_id')->references('id')->on('entite_admins')->onDelete('cascade');
            $table->foreign('fonction_agent_id')->references('id')->on('fonction_agents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
