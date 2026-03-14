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
        Schema::table('reponses', function (Blueprint $table) {
                    $table->string('hasPermission')->nullable()->change();
                    $table->string('reason')->nullable()->change();
                    $table->string('note')->nullable()->change();
                    $table->string('content')->nullable()->change();
                    $table->string('motif')->nullable()->change();
                    $table->string('preview_file')->nullable()->change();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            //
        });
    }
};
