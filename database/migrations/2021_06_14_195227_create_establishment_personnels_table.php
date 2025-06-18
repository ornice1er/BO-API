<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstablishmentPersonnelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('establishment_personnels', function (Blueprint $table) {
            $table->id();

            $table->string("lastname");
            $table->string("firstname");
            $table->string("imm");
            $table->string("sexe");
            $table->string("job");
            $table->date("date_naissance");
            $table->date("date_job");
            $table->string("address");
            $table->string("phone");
            $table->string("type");
            $table->unsignedBigInteger('etablishment_id');
            $table->foreign('etablishment_id')
            ->references('id')
            ->on('establishments')
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
        Schema::dropIfExists('establishment_personnels');
    }
}
