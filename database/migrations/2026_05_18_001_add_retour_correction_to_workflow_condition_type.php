<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM('auto','validation','rejet','complement','signature','cloture','paraphe','prevalidation','choix_sortie','correction','retour_correction')
            NOT NULL DEFAULT 'auto'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM('auto','validation','rejet','complement','signature','cloture','paraphe','prevalidation','choix_sortie','correction')
            NOT NULL DEFAULT 'auto'
        ");
    }
};
