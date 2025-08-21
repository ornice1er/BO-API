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
        Schema::create('current_officer_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreignId('unite_admin_id')->references('id')->on('unite_admins')->onDelete('cascade');
            $table->foreignId('fonction_id')->references('id')->on('fonction_agents')->onDelete('cascade');
            $table->string('statut');
            $table->timestamps();
            // Indexes for performant lookups; business constraints enforced at application level
            $table->index(['agent_id', 'statut']);
            $table->index(['unite_admin_id', 'fonction_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_officer_posts');
    }
};
