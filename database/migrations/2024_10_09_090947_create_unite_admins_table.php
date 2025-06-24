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
        Schema::create('unite_admins', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('libelle', 255);
            $table->unsignedBigInteger('type_unite_admin_id');
            $table->string('sigle', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->unsignedBigInteger('entite_admin_id')->nullable();
            $table->uuid('ua_parent_code')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->timestamps();

            // Clés étrangères
            $table->foreign('type_unite_admin_id')
                  ->references('id')
                  ->on('type_unite_admins')
                  ->onDelete('cascade');

            $table->foreign('entite_admin_id')
                  ->references('id')
                  ->on('entite_admins')
                  ->onDelete('set null');

            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null');

            // Index pour améliorer les performances
            $table->index('code');
            $table->index('libelle');
            $table->index('sigle');
            $table->index('email');
            $table->index('ua_parent_code');
            $table->index('type_unite_admin_id');
            $table->index('entite_admin_id');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unite_admins');
    }
};
