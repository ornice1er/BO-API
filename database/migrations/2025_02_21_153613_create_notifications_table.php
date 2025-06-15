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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->longText('title');
            $table->longText('content');
            $table->string('type');
            $table->boolean('is_read')->default(false);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->softDeletes(); // Ajoute la colonne deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
