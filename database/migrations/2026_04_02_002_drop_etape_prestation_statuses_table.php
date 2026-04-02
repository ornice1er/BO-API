<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('etape_prestation_statuses');
    }

    public function down(): void
    {
        Schema::create('etape_prestation_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etape_id')->constrained('etapes')->onDelete('cascade');
            $table->foreignId('prestation_id')->constrained('prestations')->onDelete('cascade');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->timestamps();
        });
    }
};
