<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValidationDesServiceAuxFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validation_des_service_aux_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('filename')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('vsa_id');
            $table->foreign('vsa_id')
            ->references('id')
            ->on('validation_des_service_auxes');
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
        Schema::dropIfExists('validation_des_service_aux_files');
    }
}
