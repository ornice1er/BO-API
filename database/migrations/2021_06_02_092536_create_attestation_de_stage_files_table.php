<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationDeStageFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_de_stage_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('filename');
            $table->string('reference');
            $table->unsignedBigInteger('as_id');
            $table->foreign('as_id')
            ->references('id')
            ->on('attestation_de_stages')
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
        Schema::dropIfExists('attestation_de_stage_files');
    }
}
