<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supprime prestation_status_id de workflow_transitions.
 *
 * Cette colonne était redondante avec status_result_id :
 *  - status_result_id est la colonne active, lue par le moteur de workflow
 *    (WorkflowStateController) pour poster le statut sur la requête.
 *  - prestation_status_id n'était lu nulle part dans le moteur.
 *
 * La cohérence "le statut choisi est valide pour cette prestation" est
 * désormais garantie au niveau applicatif (StoreWorkflowRequest / UpdateWorkflowRequest).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropForeign(['prestation_status_id']);
            $table->dropIndex('idx_wt_prestation_status');
            $table->dropColumn('prestation_status_id');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->unsignedBigInteger('prestation_status_id')
                  ->nullable()
                  ->after('status_result_id');

            $table->foreign('prestation_status_id')
                  ->references('id')
                  ->on('prestation_statuses')
                  ->nullOnDelete();

            $table->index('prestation_status_id', 'idx_wt_prestation_status');
        });
    }
};
