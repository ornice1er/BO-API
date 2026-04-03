<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 12/13 — Ajustements tables existantes pour le flux consultation médicale
 *
 * Trois tables existantes reçoivent des colonnes supplémentaires.
 * Tout est rétrocompatible : les flux 1 (habilitation) et 2 (agrément)
 * ne sont pas affectés — les valeurs par défaut maintiennent le comportement existant.
 *
 * ── AGENDAS ──────────────────────────────────────────────────────────────────
 * La table existante couvre déjà : date_start/end, requete_id, status, ua_up.
 * Elle gère la liaison RDV ↔ requête et l'état Ouvert/Clos.
 *
 * On ajoute les métadonnées de session médicale :
 *   duration_minutes  : durée unitaire d'un créneau (20 min par défaut selon le flux)
 *   max_slots         : capacité max de la session (15 à 25 par matinée selon le flux)
 *   session_type      : moment de la journée (matinee | apres_midi | journee_entiere)
 *   rdv_type          : nature du RDV (premier_rdv | second_rdv | suivi_traitement)
 *                       → distingue les RDV initiaux des second RDV demandés après
 *                         résultats d'examens (FN116) ou traitement (FN143)
 *
 * ── ETAPE_DOCUMENTS_PRODUITS ─────────────────────────────────────────────────
 * On ajoute deux flags qui différencient le comportement du flux 3 :
 *
 *   is_auto_signed  : le document est généré avec signature+cachet automatiques
 *                     sans passer par un circuit multi-acteurs (paraphe/signature).
 *                     → Flux 3 : certificat médical d'aptitude (FN84 : "le système
 *                       génère le certificat avec la signature et le cachet du
 *                       médecin inspecteur de travail")
 *                     → Flux 2 : false (circuit DGT→Ministre requis)
 *                     → Flux 1 : false (décision signée manuellement)
 *
 *   is_selectable   : le document apparaît dans le menu de choix post-consultation
 *                     (FN40/FN57/FN79/FN107 : "le système demande un choix entre
 *                     Éditer le certificat d'aptitude / Éditer un bon d'examens /
 *                     Prescrire un repos / Prescrire un traitement")
 *                     → Flux 3 : true sur les 4 types de documents de sortie
 *                     → Flux 1&2 : false (document unique, pas de menu)
 *
 * ── WORKFLOW_TRANSITIONS ─────────────────────────────────────────────────────
 * L'enum condition_type est élargi avec 'choix_sortie'.
 *
 *   choix_sortie    : déclenche l'affichage du menu de sélection de document
 *                     après "Finaliser la consultation" (FN39/FN56/FN78/FN106/FN133).
 *                     Le moteur présente alors tous les etape_document_produits
 *                     où is_selectable = true pour cette prestation.
 *                     L'agent choisit → le système génère le document correspondant.
 *
 * NOTE : La modification de l'enum condition_type se fait via ALTER TABLE brut
 * car Blueprint::change() ne supporte pas la modification d'enum sur MySQL < 8.0.
 * On conserve TOUTES les valeurs existantes (auto, validation, rejet, complement,
 * signature, cloture, paraphe, prevalidation) et on ajoute 'choix_sortie'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── AGENDAS ──────────────────────────────────────────────────────────
        Schema::table('agendas', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')
                  ->default(20)
                  ->after('description')
                  ->comment('Durée unitaire du créneau en minutes (20 min par défaut)');

            $table->unsignedInteger('max_slots')
                  ->default(20)
                  ->after('duration_minutes')
                  ->comment('Capacité max de la session (15-25 selon le flux documenté)');

            $table->enum('session_type', ['matinee', 'apres_midi', 'journee_entiere'])
                  ->default('matinee')
                  ->after('max_slots')
                  ->comment('Moment de la journée pour la session médicale');

            $table->enum('rdv_type', ['premier_rdv', 'second_rdv', 'suivi_traitement'])
                  ->default('premier_rdv')
                  ->after('session_type')
                  ->comment('Nature du RDV — distingue les 2e RDV et RDV post-traitement');
        });

        // ── ETAPE_DOCUMENTS_PRODUITS ─────────────────────────────────────────
        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->boolean('is_auto_signed')
                  ->default(false)
                  ->after('allow_correction')
                  ->comment('Doc généré avec signature+cachet auto, sans circuit multi-acteurs');

            $table->boolean('is_selectable')
                  ->default(false)
                  ->after('is_auto_signed')
                  ->comment('Apparaît dans le menu de choix post-consultation (flux 3)');
        });

        // ── WORKFLOW_TRANSITIONS — élargissement enum ────────────────────────
        // Cumule toutes les valeurs des migrations 002 et 007
        \DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM(
                'auto',
                'validation',
                'rejet',
                'complement',
                'signature',
                'cloture',
                'paraphe',
                'prevalidation',
                'choix_sortie'
            ) NOT NULL DEFAULT 'auto'
            COMMENT 'Déclencheur : choix_sortie = menu sélection doc post-consultation (flux 3)'
        ");

        // ── NOUVEAUX STATUTS FLUX 3 ──────────────────────────────────────────
        // Insérés ici pour grouper la config flux 3 dans une seule migration
        $statuts = [
            ['short_name' => 'rdv_confirme',                   'name' => 'RDV confirmé'],
            ['short_name' => 'rdv_reprogramme',                'name' => 'RDV reprogrammé'],
            ['short_name' => 'en_traitement',                  'name' => 'En traitement — consultation en cours'],
            ['short_name' => 'resultats_rendus',               'name' => 'Résultats d\'examens rendus'],
            ['short_name' => 'en_attente_2e_consultation',     'name' => 'En attente d\'une deuxième consultation'],
            ['short_name' => 'traitement_envoye',              'name' => 'Traitement prescrit — envoyé'],
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
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn([
                'duration_minutes',
                'max_slots',
                'session_type',
                'rdv_type',
            ]);
        });

        Schema::table('etape_document_produits', function (Blueprint $table) {
            $table->dropColumn(['is_auto_signed', 'is_selectable']);
        });

        // Retour à l'état migration 007 (sans choix_sortie)
        \DB::statement("
            ALTER TABLE workflow_transitions
            MODIFY COLUMN condition_type
            ENUM(
                'auto',
                'validation',
                'rejet',
                'complement',
                'signature',
                'cloture',
                'paraphe',
                'prevalidation'
            ) NOT NULL DEFAULT 'auto'
        ");

        $shortNames = [
            'rdv_confirme', 'rdv_reprogramme', 'en_traitement',
            'resultats_rendus', 'en_attente_2e_consultation', 'traitement_envoye',
        ];
        \DB::table('statuses')->whereIn('short_name', $shortNames)->delete();
    }
};
