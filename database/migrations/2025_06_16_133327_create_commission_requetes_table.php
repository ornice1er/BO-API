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
        // Schema::create('commission_requetes', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('commission_id')->constrained('commissions')->onDelete('cascade');
        //     $table->foreignId('requete_id')->constrained('requetes')->onDelete('cascade');
        //     $table->decimal('global_mark', 5, 2)->nullable();
        //     $table->string('status')->default('pending');
        //     $table->timestamps();
            
        //     $table->unique(['commission_id', 'requete_id']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_requetes');
    }
}; 