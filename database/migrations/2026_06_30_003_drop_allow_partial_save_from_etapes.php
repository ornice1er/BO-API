<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire `etapes.allow_partial_save`.
 *
 * Champ saisi dans la configuration mais lu par aucun code (ni API, ni front) :
 * il laissait croire à l'administrateur qu'il pilotait un comportement inexistant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            if (Schema::hasColumn('etapes', 'allow_partial_save')) {
                $table->dropColumn('allow_partial_save');
            }
        });
    }

    public function down(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            if (!Schema::hasColumn('etapes', 'allow_partial_save')) {
                $table->boolean('allow_partial_save')->default(false)->after('type');
            }
        });
    }
};
