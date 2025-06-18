<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('needOut')->default(false);
            $table->unsignedBigInteger('unite_admin_id')->nullable();
            $table->unsignedBigInteger('entite_admin_id')->nullable();
            $table->timestamps();
            $table->text('desc')->nullable();
            $table->tinyInteger('content_type')->default(0);
            $table->boolean('need_meeting')->default(false);
            $table->boolean('need_validation')->default(false);
            $table->boolean('signer')->default(false);
            $table->integer('start_point')->default(0);
            $table->integer('delay')->default(0);
            $table->boolean('is_automatic_delivered')->default(false);
            $table->boolean('from_pns')->default(false);

            // Relations (optionnel)
            $table->foreign('unite_admin_id')->references('id')->on('unite_admins')->onDelete('set null');
            $table->foreign('entite_admin_id')->references('id')->on('entite_admins')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestations');
    }
};
