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
        Schema::table('requete_files', function (Blueprint $table) {
                $table->string('file_path');
                $table->string('file_type')->nullable();
                $table->string('source_table')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requete_files', function (Blueprint $table) {
            //
        });
    }
};
