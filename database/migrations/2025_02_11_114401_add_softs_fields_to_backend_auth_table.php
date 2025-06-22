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

        Schema::table('departments', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });
        Schema::table('municipalities', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });
        Schema::table('districts', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });
        Schema::table('villages', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });
        Schema::table('user_settings', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });


        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
