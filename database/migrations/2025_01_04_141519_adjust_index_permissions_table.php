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

        Schema::table('permissions', function (Blueprint $table) {
            // Drop the existing unique index
            $table->dropUnique('permissions_name_guard_name_unique');

            // Add the new unique index (without length specification, as lengths require raw SQL)
            $table->unique(['name', 'guard_name', 'label_name', 'group_name'], 'permissions_name_guard_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Drop the newly created unique index
            $table->dropUnique('permissions_name_guard_name_unique');

            // Restore the original unique index
            $table->unique(['name', 'guard_name'], 'permissions_name_guard_name_unique');
        });
    }
};
