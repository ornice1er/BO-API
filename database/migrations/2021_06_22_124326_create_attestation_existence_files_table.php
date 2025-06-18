<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationExistenceFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_existence_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('filename');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('ae_id');
            $table->foreign('ae_id')
            ->references('id')->on('attestation_existences')
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
        Schema::dropIfExists('attestation_existence_files');
    }
}
