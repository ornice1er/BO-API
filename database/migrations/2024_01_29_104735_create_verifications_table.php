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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('type', ['phone', 'email']);
            $table->datetime('expired_at');
            $table->boolean('sms')->default(false);
            $table->boolean('whatsapp')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
