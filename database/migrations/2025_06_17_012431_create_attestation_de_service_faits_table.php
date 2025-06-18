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
        Schema::create('attestations_de_service_faits', function (Blueprint $table) {
            $table->id();
            $table->uuid('code');
            $table->string('name_structure');
            $table->string('phone_structure');
            $table->string('email_structure')->nullable();
            $table->string('rccm')->nullable();
            $table->string('ifu')->nullable();
            $table->string('ref_marche')->nullable();
            $table->text('message')->nullable();
            $table->date('date_contrat')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->timestamps();

            // $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestations_de_service_faits');
    }
};
