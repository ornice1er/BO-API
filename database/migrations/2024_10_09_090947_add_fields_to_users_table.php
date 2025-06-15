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
        Schema::table('users', function (Blueprint $table) {
            $table->string('code')->unique()->after('id');
            $table->string('lastname')->after('name');
            $table->string('firstname')->after('lastname');
            $table->date('birthdate')->nullable()->after('firstname');
            $table->string('birthplace')->nullable()->after('birthdate');
            $table->string('address')->nullable()->after('birthplace');
            $table->string('phone')->nullable()->after('address');
            $table->string('photo')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('photo');
            $table->boolean('is_first_connexion')->default(true)->after('is_active');
            $table->string('token')->nullable()->after('password');
            $table->string('code_otp')->nullable()->after('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
