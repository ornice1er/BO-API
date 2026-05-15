<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            $table->boolean('need_meeting')->default(false)->after('can_associate');
        });
    }

    public function down(): void
    {
        Schema::table('etapes', function (Blueprint $table) {
            $table->dropColumn('need_meeting');
        });
    }
};
