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
        Schema::table('requetes', function (Blueprint $table) {
            // Add closed_at column if it doesn't exist
            if (!Schema::hasColumn('requetes', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            if (Schema::hasColumn('requetes', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
        });
    }
};
