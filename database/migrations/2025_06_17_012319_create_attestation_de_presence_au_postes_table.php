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
        Schema::create('attestations_de_presence_au_postes', function (Blueprint $table) {
            $table->id();
            $table->uuid('code');
            $table->string('identity');
            $table->string('matricule')->nullable();
            $table->string('structure')->nullable();
            $table->string('corporate')->nullable();
            $table->date('date_job')->nullable();
            $table->string('sex')->nullable();
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->timestamps();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('unite_admin_id')->nullable();
            $table->date('birthday')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('function')->nullable();
            $table->string('category')->nullable();
            $table->string('scale')->nullable();
            $table->string('level')->nullable();
            $table->string('bride')->nullable();

            // $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('set null');
            // $table->foreign('unite_admin_id')->references('id')->on('unites_admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestations_de_presence_au_postes');
    }
};
