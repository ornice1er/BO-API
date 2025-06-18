<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name_chief');
            $table->string('name_ent');
            $table->string('email_ent');
            $table->string('activite_principale_ent');
            $table->string('departement');
            $table->string('commune');
            $table->string('arrondissement');
            $table->string('quartier');
            $table->string('rue');
            $table->string('address');
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
        Schema::dropIfExists('companies');
    }
}
