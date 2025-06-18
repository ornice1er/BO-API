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
        Schema::create('entite_admins', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('libelle');
            $table->string('sigle')->nullable();
            $table->unsignedBigInteger('type_entity_id');
            $table->timestamps();

            // Index et clé étrangère
            $table->index('type_entity_id');
            $table->foreign('type_entity_id')->references('id')->on('type_entites')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entite_admins');
    }
};
