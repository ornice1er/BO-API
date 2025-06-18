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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->default('0'); // ou tinyInteger si tu veux un champ numérique
            $table->text('description')->nullable();
            $table->boolean('usager_response')->default(0);
            $table->boolean('has_notif')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('requete_id')->nullable();
            $table->string('priority')->nullable();
            $table->unsignedBigInteger('ua_up')->nullable();

            // Clés étrangères (à activer si les tables existent)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('requete_id')->references('id')->on('requetes')->onDelete('set null')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
