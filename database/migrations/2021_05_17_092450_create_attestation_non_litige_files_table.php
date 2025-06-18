<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttestationNonLitigeFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attestation_non_litige_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('filename');
            $table->string('reference');
            $table->unsignedBigInteger('atn_id');
            $table->foreign('atn_id')
            ->references('id')
            ->on('attestation_non_litiges')
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
        Schema::dropIfExists('attestation_non_litige_files');
    }
}
