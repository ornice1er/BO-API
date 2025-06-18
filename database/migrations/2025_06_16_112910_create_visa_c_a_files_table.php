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
        Schema::create('visa_c_a_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('filename');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('visa_ca_id');
            $table->timestamps();

            // Index et clé étrangère
            $table->index('visa_ca_id');
            $table->index('type');
            $table->foreign('visa_ca_id')->references('id')->on('visa_c_a_s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_c_a_files');
    }
};
