<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValidationDesServiceAuxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validation_des_service_auxes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('identity');
            $table->string('sex');
            $table->string('bride');
            $table->string('birthplace');
            $table->date('birthday');
            $table->string('entity');
            $table->string('job');
            $table->string('rank');
            $table->string('matricule');
            $table->date('job_date');
            $table->string('function');
            $table->string('length_of_service');
            $table->date('retirement_date');
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
        Schema::dropIfExists('validation_des_service_auxes');
    }
}
