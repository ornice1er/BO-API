<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->unsignedBigInteger('planning_slot_id')
                  ->nullable()
                  ->after('eps_id')
                  ->comment('Créneau RDV choisi par l\'usager sur le PNS — lu depuis meta.planning_slot_id');

            $table->foreign('planning_slot_id')
                  ->references('id')->on('planning_slots')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropForeign(['planning_slot_id']);
            $table->dropColumn('planning_slot_id');
        });
    }
};
