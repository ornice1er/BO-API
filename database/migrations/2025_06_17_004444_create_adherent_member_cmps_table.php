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
        Schema::create('Adherant_member_cmps', function (Blueprint $table) {
            $table->id();
            $table->date('start_date')->nullable();
            $table->string('identity')->nullable();
            $table->date('birthday')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('sex')->nullable();
            $table->string('identity_mother')->nullable();
            $table->string('profession')->nullable();
            $table->string('filiation')->nullable();
            $table->unsignedBigInteger('adh_id')->nullable(); // référence à adherant_cmps
            $table->timestamps();

            $table->foreign('adh_id')->references('id')->on('adherant_cmps')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Adherant_member_cmps');
    }
};
