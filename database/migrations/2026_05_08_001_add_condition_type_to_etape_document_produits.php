<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->enum('condition_type', [
                'auto', 'validation', 'rejet', 'complement',
                'signature', 'cloture', 'paraphe', 'prevalidation', 'choix_sortie'
            ])->default('validation')->after('generate_from')
              ->comment('Condition de transition à déclencher lors de recupDoc');
        });
    }

    public function down(): void
    {
        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->dropColumn('condition_type');
        });
    }
};
