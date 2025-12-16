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
        // Schema::table('requetes', function (Blueprint $table) {
        //    $table->string('lastname')->nullable();
        //     $table->string('firstname')->nullable();
        //     $table->string('identity')->nullable();
        //     $table->string('npi')->nullable();
        //     $table->string('rccm')->nullable();
        //     $table->string('ifu')->nullable();
        //     $table->string('raison_sociale')->nullable();
        //     $table->longText('message')->nullable();
        //     $table->date('date_job')->nullable();
        //     $table->date('birthdate')->nullable();
        //     $table->string('function')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
           
        });
    }
};
