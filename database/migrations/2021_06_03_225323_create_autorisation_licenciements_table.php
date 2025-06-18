<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutorisationLicenciementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autorisation_licenciements', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name_structure');
            $table->string('phone_structure');
            $table->string('email_structure');
            $table->string('rccm');
            $table->string('ifu');
            $table->string('identity');
            $table->string('email');
            $table->string('phone');
            $table->date('date_job');
            $table->string('type');
            $table->string('profession');
            $table->string('salary_type');
            $table->longText('message');
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
        Schema::dropIfExists('autorisation_licenciements');
    }
}
