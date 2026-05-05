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
            $table->unsignedBigInteger('municipality_id')->nullable()->after('eps_id');
            $table->unsignedBigInteger('department_id')->nullable()->after('eps_id');
            $table->foreign('municipality_id')->references('id')->on('municipalities')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['municipality_id', 'department_id']);
        });
    }
};
