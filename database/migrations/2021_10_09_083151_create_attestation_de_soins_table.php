<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationDeSoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_de_soins', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('subscriptor');
            $table->string('contract_number');
            $table->string('insured');
            $table->string('cmps_number');
            $table->string('sick_identiy');
            $table->date('sick_birthday');
            $table->string('sick_phone');
            $table->date('event_date');
            $table->string('affection');
            $table->string('phone');
            $table->string('email');
            $table->unsignedBigInteger('requete_id');
            $table->foreign('requete_id')
            ->references('id')
            ->on('requetes');
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
        Schema::dropIfExists('attestation_de_soins');
    }
}
