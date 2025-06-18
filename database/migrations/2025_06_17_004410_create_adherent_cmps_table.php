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
        Schema::create('Adherant_cmps', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('identity')->nullable();
            $table->date('birthday')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('address')->nullable();
            $table->string('assoc_name')->nullable();
            $table->string('economic_activity')->nullable();
            $table->string('profession')->nullable();
            $table->string('adherance_fee')->nullable();
            $table->date('adherance_fee_date')->nullable();
            $table->string('first_contributtion')->nullable();
            $table->date('first_contributtion_date')->nullable();
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->timestamps();

            $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Adherant_cmps');
    }
};
