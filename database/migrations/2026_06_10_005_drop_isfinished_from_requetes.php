<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suppression de `requetes.isFinished` : colonne écrite mais jamais lue.
 * L'état « terminé » est déjà porté par `isTreated` + `current_etape.is_terminal`.
 *
 * ⚠️ À lancer APRÈS déploiement du code qui n'écrit plus isFinished.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (Schema::hasColumn('requetes', 'isFinished')) {
                $table->dropColumn('isFinished');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (!Schema::hasColumn('requetes', 'isFinished')) {
                $table->boolean('isFinished')->default(false);
            }
        });
    }
};
