<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFonctionAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fonction_agents', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('libelle', 255);
            $table->unsignedBigInteger('entite_admin_id')->nullable();
            $table->foreign('entite_admin_id')
                ->references('id')
                ->on('entite_admins')
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

        Schema::dropIfExists('fonction_agents');
    }
}
