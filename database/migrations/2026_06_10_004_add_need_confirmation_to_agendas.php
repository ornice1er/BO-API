<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drapeau « confirmation de l'usager demandée » lors de la création d'un RDV.
 * Si true, on attend un retour usager (variable `retour_rdv` depuis le PNS)
 * pour mettre à jour le statut du RDV.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            if (!Schema::hasColumn('agendas', 'need_confirmation')) {
                $table->boolean('need_confirmation')->default(false)->after('usager_response');
            }
            // Commentaire renvoyé par l'usager avec sa réponse (retour_rdv)
            if (!Schema::hasColumn('agendas', 'usager_comment')) {
                $table->text('usager_comment')->nullable()->after('need_confirmation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            foreach (['need_confirmation', 'usager_comment'] as $col) {
                if (Schema::hasColumn('agendas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
