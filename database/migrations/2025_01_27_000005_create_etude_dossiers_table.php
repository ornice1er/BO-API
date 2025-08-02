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
        Schema::create('etude_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_member_id')->constrained()->onDelete('cascade');
            $table->foreignId('commission_requete_id')->constrained()->onDelete('cascade');
            $table->decimal('mark', 5, 2)->nullable();
            $table->string('status')->default('pending');
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Index unique pour éviter qu'un membre évalue plusieurs fois la même requête dans la même commission
            $table->unique(['commission_member_id', 'commission_requete_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etude_dossiers');
    }
}; 