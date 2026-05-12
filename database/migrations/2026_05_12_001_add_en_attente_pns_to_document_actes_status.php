<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE document_actes
            MODIFY COLUMN status
            ENUM('en_edition','en_circuit','complet','rejete','en_attente_pns')
            NOT NULL DEFAULT 'en_edition'
        ");
    }

    public function down(): void
    {
        // Remettre les lignes en_attente_pns en en_edition avant de retirer la valeur
        DB::table('document_actes')
            ->where('status', 'en_attente_pns')
            ->update(['status' => 'en_edition']);

        DB::statement("
            ALTER TABLE document_actes
            MODIFY COLUMN status
            ENUM('en_edition','en_circuit','complet','rejete')
            NOT NULL DEFAULT 'en_edition'
        ");
    }
};
