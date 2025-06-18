<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutorisationDeStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autorisation_de_stages', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('identity');
            $table->date('birthday');
            $table->string('phone');
            $table->string('email');
            $table->string('school');
            $table->string('domain');
            $table->longText('message');
            $table->unsignedBigInteger('requete_id');
            $table->foreign('requete_id')
            ->references('id')
            ->on('requetes')
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
        Schema::dropIfExists('autorisation_de_stages');
    }
}
