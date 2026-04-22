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
        Schema::table('json', function (Blueprint $table) {
              // Convertir les anciennes valeurs string en JSON array
    DB::table('projects')->get()->each(function ($project) {
        if (!empty($project->prestations)) {
            // Si c'est déjà du JSON valide, on skip
            json_decode($project->prestations);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $codes = array_map('trim', explode(',', $project->prestations));
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['prestations' => json_encode($codes)]);
            }
        }
    });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('json', function (Blueprint $table) {
            //
        });
    }
};
