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
            // $table->unsignedBigInteger('agent_id')->nullable()->after('code');
            // $table->unsignedBigInteger('entite_admin_id')->nullable()->after('agent_id');
            // $table->string('lastname')->after('name');
            // $table->string('firstname')->after('lastname');
            // $table->date('birthdate')->nullable()->after('firstname');
            // $table->string('birthplace')->nullable()->after('birthdate');
            // $table->string('address')->nullable()->after('birthplace');
            // $table->string('phone')->nullable()->after('address');
            // $table->string('photo')->nullable()->after('phone');
            // $table->boolean('is_active')->default(true)->after('photo');
            // $table->boolean('is_first_connexion')->default(true)->after('is_active');
            // $table->string('token')->nullable()->after('password');
            // $table->string('code_otp')->nullable()->after('token');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('entite_admin_id')->nullable();
            $table->string('username');
            $table->boolean('first_signin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('connected')->default(false);
            $table->string('doc_pass')->nullable();
            $table->text('access_token')->nullable();
            $table->text('token')->nullable();
            $table->boolean('is_trade')->default(false);


            // Clés étrangères
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->foreign('entite_admin_id')->references('id')->on('entite_admins')->onDelete('set null');

            // Index pour optimiser les requêtes
            $table->index(['username', 'email']);
            $table->index(['is_active', 'connected']);
            $table->index('agent_id');
            $table->index('entite_admin_id');
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
