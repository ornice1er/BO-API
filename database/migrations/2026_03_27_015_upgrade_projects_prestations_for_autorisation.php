<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 15 — Enrichissement `projects` pour le flux 4
 *                Autorisation de diriger / Autorisation d'enseigner
 *
 * CONTEXTE
 * ─────────
 * Ce flux introduit le concept de SESSION D'ÉTUDE : un regroupement de
 * requêtes traitées ensemble par le comité DDEMP lors d'une même période.
 * Le Ministre valide et clôture la SESSION (UC011), pas les requêtes une par une.
 * L'arrêté est rattaché à la session, pas à une requête individuelle.
 *
 * La table `projects` existante modélise déjà ce concept partiellement :
 *   ✓ title, description, date_start, date_end
 *   ✓ status enum('open', 'closed')
 *   ✓ project_requete (pivot vers les requêtes de la session)
 *   ✓ requetes.project_id (FK nullable déjà présente)
 *
 * Il manque les colonnes spécifiques au flux 4.
 *
 * ── PROJECTS (sessions d'étude) ──────────────────────────────────────────────
 *
 * status          → enum élargi : open | en_cours_etude | en_attente_arrete | closed
 *                   L'enum 'open/closed' existant est insuffisant pour les
 *                   statuts intermédiaires de la session (UC009→UC011).
 *
 * prestation_id   → FK vers prestations. Une session regroupe les demandes
 *                   D'UN SEUL TYPE : soit "Autorisation de diriger",
 *                   soit "Autorisation d'enseigner" (pas les deux mélangées).
 *                   UC009 : "choix du type de demande (Enseigner ou diriger)"
 *
 * arrete_reference → Référence de l'arrêté signé par l'autorité (UC010 step 4)
 *                    ex: "Arrêté N°2026-042/MTFP/DC/SGM/DDEMP"
 *
 * arrete_file      → Chemin du fichier arrêté uploadé (UC010) — distinct de
 *                    `filename` (document de session générique) et
 *                    `closing_filename` (document de clôture)
 *
 * arrete_date_signature → Date de signature de l'arrêté par l'autorité (UC010)
 *
 * periode_etude    → Libellé de la période d'étude des dossiers par la commission
 *                    ex: "Session mars 2026" — apparaît dans l'autorisation
 *                    générée (UC012 : "portant la référence de l'arrêté, l'année,
 *                    la période d'étude des dossiers par la commission")
 *
 * ministre_validated_at → Horodatage de la validation par le Ministre (UC011)
 *
 * ── CE QUI N'EST PAS MODIFIÉ ─────────────────────────────────────────────────
 *
 * prestations.type_demande → PAS AJOUTÉ. Chaque prestation (diriger, enseigner)
 *                            est déjà identifiée par son prestation_id — inutile
 *                            de dupliquer cette information dans une colonne séparée.
 * requetes.project_id  → déjà présent et nullable — aucun changement
 * project_requete      → pivot existant — aucun changement
 * agendas              → couvre l'entretien UC004 sans modification
 * etape_documents_produits → couvre la génération de l'autorisation UC012
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── PROJECTS ─────────────────────────────────────────────────────────
        Schema::table('projects', function (Blueprint $table) {

            // Élargissement du status pour les étapes intermédiaires de session
            \DB::statement("
                ALTER TABLE projects
                MODIFY COLUMN status
                ENUM(
                    'open',
                    'en_cours_etude',
                    'en_attente_arrete',
                    'closed'
                ) NOT NULL DEFAULT 'open'
                COMMENT 'Statut de la session — élargi pour flux autorisation diriger/enseigner'
            ");

            $table->unsignedBigInteger('prestation_id')
                  ->nullable()
                  ->after('title')
                  ->comment('Type de demande regroupé dans cette session (diriger | enseigner)');

            $table->string('arrete_reference')
                  ->nullable()
                  ->after('closing_filename')
                  ->comment('Référence de l\'arrêté signé (UC010 step 4)');

            $table->string('arrete_file')
                  ->nullable()
                  ->after('arrete_reference')
                  ->comment('Chemin du fichier arrêté uploadé par l\'agent DDEMP (UC010)');

            $table->date('arrete_date_signature')
                  ->nullable()
                  ->after('arrete_file')
                  ->comment('Date de signature de l\'arrêté par l\'autorité (UC010)');

            $table->string('periode_etude')
                  ->nullable()
                  ->after('arrete_date_signature')
                  ->comment('Libellé période ex: "Session mars 2026" — imprimé dans l\'autorisation UC012');

            $table->timestamp('ministre_validated_at')
                  ->nullable()
                  ->after('periode_etude')
                  ->comment('Horodatage de la validation + clôture par le Ministre (UC011)');

            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->nullOnDelete();

            $table->index(['prestation_id', 'status'], 'idx_project_prestation_status');
        });

        // ── NOUVEAUX STATUTS FLUX 4 ───────────────────────────────────────────
        $statuts = [
            ['short_name' => 'pris_en_charge',          'name' => 'Pris en charge — Agent CS'],
            ['short_name' => 'en_attente_entretien',    'name' => 'En attente de l\'entretien'],
            ['short_name' => 'en_cours_etude',          'name' => 'En cours d\'étude — DDEMP'],
            ['short_name' => 'corrige',                 'name' => 'Corrigé par l\'usager'],
            ['short_name' => 'en_attente_finalisation', 'name' => 'En attente de finalisation'],
            ['short_name' => 'en_attente_arrete',       'name' => 'En attente de l\'arrêté'],
            ['short_name' => 'recale',                  'name' => 'Recalé'],
            ['short_name' => 'finalise',                'name' => 'Finalisé'],
        ];

        foreach ($statuts as $s) {
            \DB::table('statuses')->insertOrIgnore([
                ...$s,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['prestation_id']);
            $table->dropIndex('idx_project_prestation_status');
            $table->dropColumn([
                'prestation_id',
                'arrete_reference',
                'arrete_file',
                'arrete_date_signature',
                'periode_etude',
                'ministre_validated_at',
            ]);
        });

        \DB::statement("
            ALTER TABLE projects
            MODIFY COLUMN status
            ENUM('open', 'closed') NOT NULL DEFAULT 'open'
        ");

        $shortNames = [
            'pris_en_charge', 'en_attente_entretien', 'en_cours_etude',
            'corrige', 'en_attente_finalisation', 'en_attente_arrete',
            'recale', 'finalise',
        ];
        \DB::table('statuses')->whereIn('short_name', $shortNames)->delete();
    }
};
