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
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('durer')->default(120);
            $table->integer('seance_interactive')->default(120);
            $table->integer('dure_deuiduite')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['durer', 'seance_interactive', 'dure_deuiduite']);
        });
    }
};
