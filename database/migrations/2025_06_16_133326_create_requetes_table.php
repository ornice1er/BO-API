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
        Schema::create('requetes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->boolean('isTreated')->default(false);
            $table->boolean('isAutorized')->default(false);
            $table->boolean('isLocked')->default(false);
            $table->boolean('isFinished')->default(false);
            $table->string('email')->nullable();
            $table->boolean('needCorrection')->default(false);
            $table->string('filename')->nullable();
            $table->text('finaleResponse')->nullable();
            $table->unsignedBigInteger('prestation_id')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('isDeclined')->default(false);
            $table->json('header')->nullable();
            $table->text('attach')->nullable();
            $table->text('comment')->nullable();
            $table->longText('content')->nullable();
            $table->longText('content2')->nullable();
            $table->longText('content3')->nullable();
            $table->boolean('hasReachedAgreement')->default(false);
            $table->timestamps();
            $table->integer('status')->default(0);
            $table->json('step_contents')->nullable();

            // Foreign key (optional)
            // $table->foreign('prestation_id')->references('id')->on('prestations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requetes');
    }
};
