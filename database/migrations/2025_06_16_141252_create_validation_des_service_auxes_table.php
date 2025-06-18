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
        Schema::create('validation_des_service_auxes', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('identity');
            $table->string('sex');
            $table->string('bride')->nullable();
            $table->string('birthplace')->nullable();
            $table->date('birthday')->nullable();
            $table->string('entity')->nullable();
            $table->string('job')->nullable();
            $table->string('rank')->nullable();
            $table->string('matricule')->nullable();
            $table->date('job_date')->nullable();
            $table->string('status')->nullable();
            $table->string('function')->nullable();
            $table->string('length_of_service')->nullable();
            $table->date('retirement_date')->nullable();
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->unsignedBigInteger('cnr_id')->nullable();
            $table->timestamps();

            // Foreign keys (optionnel)
            $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('set null');
            // $table->foreign('cnr_id')->references('id')->on('cnrs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_des_service_auxes');
    }
};
