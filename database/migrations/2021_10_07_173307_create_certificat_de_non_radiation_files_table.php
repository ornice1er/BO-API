<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatDeNonRadiationFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificat_de_non_radiation_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('filename')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('cnr_id');
            $table->foreign('cnr_id')
            ->references('id')
            ->on('certificat_de_non_radiations');
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
        Schema::dropIfExists('certificat_de_non_radiation_files');
    }
}
