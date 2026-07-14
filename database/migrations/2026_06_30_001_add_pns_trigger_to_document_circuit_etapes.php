<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet à une étape du circuit documentaire de déclencher le PNS.
 *
 * Cas d'usage : sur une action `signature`, demander au PNS de générer /
 * récupérer le document signé (comme le fait `workflow_transitions.can_act_pns`
 * pour les transitions de requête).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_circuit_etapes', function (Blueprint $table) {
            if (!Schema::hasColumn('document_circuit_etapes', 'can_act_pns')) {
                $table->boolean('can_act_pns')->default(false)->after('action_type');
            }
            if (!Schema::hasColumn('document_circuit_etapes', 'decision')) {
                $table->string('decision')->nullable()->after('can_act_pns');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_circuit_etapes', function (Blueprint $table) {
            foreach (['can_act_pns', 'decision'] as $col) {
                if (Schema::hasColumn('document_circuit_etapes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
