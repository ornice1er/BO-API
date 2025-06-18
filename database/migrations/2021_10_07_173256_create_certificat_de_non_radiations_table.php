<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatDeNonRadiationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificat_de_non_radiations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('identity');
            $table->string('object');
            $table->longtext('message');
            $table->string('address');
            $table->string('destinator');
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
        Schema::dropIfExists('certificat_de_non_radiations');
    }
}
