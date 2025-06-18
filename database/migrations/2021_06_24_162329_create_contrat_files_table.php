<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContratFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contrat_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('filename'); 
            $table->unsignedBigInteger('contrat_id');
            $table->foreign('contrat_id')
            ->references('id')->on('contrat_ps')
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
        Schema::dropIfExists('contrat_files');
    }
}
