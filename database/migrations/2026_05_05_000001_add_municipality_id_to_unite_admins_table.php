<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unite_admins', function (Blueprint $table) {
            $table->unsignedBigInteger('municipality_id')->nullable()->after('department_id');
            $table->foreign('municipality_id')
                  ->references('id')
                  ->on('municipalities')
                  ->onDelete('set null');
            $table->index('municipality_id');
        });
    }

    public function down(): void
    {
        Schema::table('unite_admins', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropIndex(['municipality_id']);
            $table->dropColumn('municipality_id');
        });
    }
};
