<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstablishmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('email_structure');
            $table->string('name_respo');
            $table->string('quality_respo');
            $table->string('type_etablissement');
            $table->date('date_ouv');
            $table->string('den_com');
            $table->text('conv_collect_app');
            $table->text('activite_principale');
            $table->string('forme_juridique');

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')
            ->references('id')
            ->on('companies')
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
        Schema::dropIfExists('establishments');
    }
}
