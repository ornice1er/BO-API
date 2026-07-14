<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire `etapes.is_terminal`.
 *
 * Les étapes sont globales et partagées entre e-services : une même étape peut être
 * finale pour l'un et intermédiaire pour l'autre. Le drapeau était donc structurellement
 * faux et provoquait des clôtures prématurées.
 *
 * La terminalité est désormais déduite du graphe, par prestation :
 * @see \App\Http\Repositories\RequeteRepository::estEtapeTerminale()
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            if (Schema::hasColumn('etapes', 'is_terminal')) {
                $table->dropColumn('is_terminal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            if (!Schema::hasColumn('etapes', 'is_terminal')) {
                $table->boolean('is_terminal')->default(false)->after('type');
            }
        });
    }
};
