<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reponses', function (Blueprint $table) {
            $table->id();
            $table->boolean('hasPermission');
            $table->text('reason');
            $table->text('observation');
            $table->text('note')->nullable();
            $table->text('content')->nullable();
            $table->text('motif')->nullable();
            $table->text('preview_file')->nullable();
            $table->unsignedBigInteger('requete_id');
            $table->foreign('requete_id')
            ->references('id')
            ->on('requetes')
            ->onDelete("cascade")->onUpdate("cascade");
            $table->unsignedBigInteger('unite_admin_id');
            $table->foreign('unite_admin_id')
            ->references('id')
            ->on('unite_admins')
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
        Schema::dropIfExists('reponses');
    }
}
