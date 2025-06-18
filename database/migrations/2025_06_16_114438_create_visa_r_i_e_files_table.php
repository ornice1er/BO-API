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
        Schema::create('visa_r_i_e_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('Type de fichier (ex: passeport, photo, attestation)');
            $table->string('filename')->comment('Nom du fichier');
            $table->string('reference')->nullable()->comment('Référence du document');
            $table->foreignId('visa_rie_id')
                  ->constrained('visa_r_i_e_s')
                  ->onDelete('cascade')
                  ->comment('ID du visa RIE associé');
            $table->string('file_path')->nullable()->comment('Chemin du fichier stocké');
            $table->string('mime_type')->nullable()->comment('Type MIME du fichier');
            $table->bigInteger('file_size')->nullable()->comment('Taille du fichier en octets');
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['visa_rie_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_r_i_e_files');
    }
};
