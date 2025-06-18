<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationExistencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_existences', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name_structure');
            $table->string('phone_structure');
            $table->string('email_structure');
            $table->string('rccm');
            $table->string('ifu');
            $table->string('name_respo');
            $table->string('phone_respo')->nullable();
            $table->date('date_creation');
            $table->unsignedBigInteger('requete_id');
            $table->foreign('requete_id')
            ->references('id')->on("requetes")
            ;
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
        Schema::dropIfExists('attestation_existences');
    }
}
