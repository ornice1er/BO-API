<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 14/14 — Liaison explicite `requete_files` ↔ `etape_documents`
 *
 * CONTEXTE
 * ─────────
 * Avant cette migration, la table `requete_files` ne sait pas QUELLE pièce
 * configurée dans `etape_documents` elle satisfait. La colonne `file_type`
 * (varchar libre) joue ce rôle de façon implicite — mais ce couplage par
 * chaîne de caractères est fragile : une faute de frappe, un renommage de slug,
 * et la vérification de complétude du dossier retourne un faux négatif.
 *
 * APRÈS CETTE MIGRATION
 * ──────────────────────
 * Chaque fichier uploadé est relié par FK à la ligne `etape_documents` qu'il
 * satisfait. La vérification de complétude devient une simple jointure SQL :
 *
 *   SELECT ed.name, ed.is_required,
 *          rf.id AS fourni, rf.is_valid
 *   FROM etape_documents ed
 *   LEFT JOIN requete_files rf
 *          ON rf.etape_document_id = ed.id
 *         AND rf.requete_id = :requete_id
 *   WHERE ed.prestation_id = :prestation_id
 *     AND ed.etape_id      = :etape_id
 *   -- Dossier incomplet si : ed.is_required = 1 AND rf.id IS NULL
 *   -- Pièce invalide si     : rf.id IS NOT NULL AND rf.is_valid = 0
 *
 * COLONNES AJOUTÉES
 * ──────────────────
 * etape_document_id  → FK nullable vers etape_documents.id
 *                      Nullable car les fichiers existants en base n'ont pas
 *                      encore de correspondance — on ne casse pas l'existant.
 *                      SET NULL on delete : si la config est supprimée,
 *                      le fichier reste mais perd son lien (pas de cascade).
 *
 * etape_id           → Dénormalisé depuis etape_documents pour requêtes rapides
 *                      ("tous les fichiers uploadés à l'étape X pour la requête Y")
 *                      sans avoir à joindre etape_documents à chaque fois.
 *
 * validated_by       → ID de l'agent qui a posé is_valid = true ou false
 *                      (actuellement absent — l'agent valide mais on ne sait pas qui)
 *
 * validated_at       → Horodatage de la validation
 *
 * rejection_reason   → Motif de rejet si is_valid = false
 *                      (l'agent peut annoter pourquoi la pièce est refusée)
 *
 * RÉTROCOMPATIBILITÉ
 * ───────────────────
 * Toutes les colonnes sont nullable ou ont une valeur par défaut.
 * Les requete_files existants conservent etape_document_id = NULL
 * et continuent de fonctionner via file_type (varchar) comme avant.
 * On peut migrer les données existantes progressivement via un script
 * séparé qui fait le matching file_type ↔ etape_documents.slug.
 *
 * INDEX
 * ──────
 * idx_rf_etape_doc    → accélère la jointure de vérification de complétude
 * idx_rf_requete_etape → accélère "toutes les pièces d'une requête à une étape"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requete_files', function (Blueprint $table) {

            // ── Lien vers la configuration de la pièce ───────────────────
            $table->unsignedBigInteger('etape_document_id')
                  ->nullable()
                  ->after('requete_id')
                  ->comment('FK vers etape_documents — quelle pièce configurée ce fichier satisfait');

            // ── Dénormalisation etape_id pour requêtes rapides ────────────
            $table->unsignedBigInteger('etape_id')
                  ->nullable()
                  ->after('etape_document_id')
                  ->comment('Étape à laquelle le fichier a été uploadé (dénormalisé)');

            // ── Traçabilité de la validation ──────────────────────────────
            $table->unsignedBigInteger('validated_by')
                  ->nullable()
                  ->after('is_valid')
                  ->comment('ID user (agent) qui a validé ou rejeté la pièce');

            $table->timestamp('validated_at')
                  ->nullable()
                  ->after('validated_by')
                  ->comment('Horodatage de la décision de validation');

            $table->string('rejection_reason')
                  ->nullable()
                  ->after('validated_at')
                  ->comment('Motif de rejet si is_valid = false');

            // ── Clés étrangères ───────────────────────────────────────────
            $table->foreign('etape_document_id')
                  ->references('id')
                  ->on('etape_documents')
                  ->nullOnDelete();

            $table->foreign('etape_id')
                  ->references('id')
                  ->on('etapes')
                  ->nullOnDelete();

            $table->foreign('validated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(
                ['requete_id', 'etape_document_id'],
                'idx_rf_etape_doc'
            );

            $table->index(
                ['requete_id', 'etape_id', 'is_valid'],
                'idx_rf_requete_etape'
            );
        });
    }

    public function down(): void
    {
        Schema::table('requete_files', function (Blueprint $table) {
            $table->dropForeign(['etape_document_id']);
            $table->dropForeign(['etape_id']);
            $table->dropForeign(['validated_by']);

            $table->dropIndex('idx_rf_etape_doc');
            $table->dropIndex('idx_rf_requete_etape');

            $table->dropColumn([
                'etape_document_id',
                'etape_id',
                'validated_by',
                'validated_at',
                'rejection_reason',
            ]);
        });
    }
};
