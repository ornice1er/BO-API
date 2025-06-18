<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationDePresenceAuPostesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_de_presence_au_postes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('identity');
            $table->string('matricule');
            $table->string('phone');
            $table->string('email');
            $table->string('structure');
            $table->unsignedBigInteger('requete_id');
            $table->foreign('requete_id')
            ->references('id')
            ->on('requetes')
            ->onDelete("cascade")->onUpdate("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attestation_de_precence_au_postes');
    }
}
