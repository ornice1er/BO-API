<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationDeSoinFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_de_soin_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('filename')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('ascmps_id');
            $table->foreign('ascmps_id')
            ->references('id')
            ->on('attestation_de_soins');
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
        Schema::dropIfExists('attestation_de_soin_files');
    }
}
