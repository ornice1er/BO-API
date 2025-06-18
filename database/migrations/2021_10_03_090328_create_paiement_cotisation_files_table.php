<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaiementCotisationFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paiement_cotisation_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('filename')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('pc_id');
            $table->foreign('pc_id')
            ->references('id')
            ->on('paiement_cotisations');
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
        Schema::dropIfExists('paiement_cotisation_files');
    }
}
