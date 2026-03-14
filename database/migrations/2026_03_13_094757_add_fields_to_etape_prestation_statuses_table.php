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
        Schema::table('etape_prestation_statuses', function (Blueprint $table) {
        $table->string('adding_fields')->nullable();
        $table->string('events')->nullable();
        $table->string('banettes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etape_prestation_statuses', function (Blueprint $table) {
            //
        });
    }
};
