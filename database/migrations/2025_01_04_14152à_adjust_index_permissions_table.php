<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (DB::select("SHOW INDEX FROM `permissions` WHERE Key_name = 'permissions_name_guard_name_unique'")) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique('permissions_name_guard_name_unique');
            });
        }
        Schema::table('permissions', function (Blueprint $table) {
            $table->unique(['name', 'guard_name', 'group_name', 'label_name']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
