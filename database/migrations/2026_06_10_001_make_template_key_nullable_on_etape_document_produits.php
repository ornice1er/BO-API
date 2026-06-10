<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * template_key n'est obligatoire que lorsque le document est généré
 * depuis le système (generate_from = system). Pour les documents PNS,
 * la colonne doit accepter NULL.
 *
 * SQL brut volontaire : doctrine/dbal n'est pas installé, ->change()
 * n'est donc pas disponible sous Laravel 10.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `etape_document_produits` MODIFY `template_key` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `etape_document_produits` MODIFY `template_key` VARCHAR(255) NOT NULL');
    }
};
