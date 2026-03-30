<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 13/13 — Création de `planning_slots`
 *
 * Unique nouvelle table introduite par le flux 3 (consultation médicale).
 *
 * POURQUOI UNE TABLE DÉDIÉE ET PAS JUSTE `agendas` ?
 * ────────────────────────────────────────────────────
 * `agendas` représente UN RDV CONCRET lié à UNE REQUÊTE (requete_id non null).
 * `planning_slots` représente L'OFFRE DE CRÉNEAUX DISPONIBLES — des plages
 * horaires proposées au requérant avant qu'il choisisse, et potentiellement
 * jamais associées à une requête si personne ne les réserve.
 *
 * Deux moments du flux exploitent cette table :
 *   FN25 (annulation requérant) : "le système affiche les créneaux disponibles"
 *   FA3.7 (annulation DSSMST)   : "le système affiche le planning des RDV en
 *                                  proposant le RDV le plus proche disponible"
 *   FN124/FN149                 : même logique pour les 2e RDV
 *
 * CONTRAINTES MÉTIER DOCUMENTÉES
 * ────────────────────────────────
 *   - Durée d'une consultation : 20 minutes (→ duration_minutes)
 *   - Capacité par matinée     : 15 à 25 consultations (→ max_slots / slots_booked)
 *   - Réservation atomique     : deux requérants ne peuvent pas choisir le même
 *     créneau simultanément → géré par une contrainte unique sur
 *     (unite_admin_id, slot_date, heure_debut) et un verrou optimiste via
 *     `slots_booked` incrémenté en transaction.
 *
 * COLONNES CLÉS
 * ─────────────
 * unite_admin_id  : unité administrative qui ouvre le planning (DSSMST)
 * prestation_id   : prestation concernée (nullable = slot global)
 * slot_date       : date de la session
 * heure_debut     : heure de début du créneau (TIME)
 * duration_minutes: durée du créneau (20 min par défaut)
 * max_slots       : nombre max de consultations sur ce créneau/session
 * slots_booked    : compteur de réservations actuelles (< max_slots = disponible)
 * is_available    : flag de disponibilité manuel (agent peut fermer un créneau)
 * session_type    : matinee | apres_midi | journee_entiere
 * agenda_id       : lien vers l'entrée `agendas` une fois le RDV confirmé
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_slots', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unite_admin_id')
                  ->comment('Unité administrative qui propose ce créneau (DSSMST)');

            $table->unsignedBigInteger('prestation_id')
                  ->nullable()
                  ->comment('Prestation concernée (null = créneau générique)');

            $table->date('slot_date')
                  ->comment('Date de la session de consultations');

            $table->time('heure_debut')
                  ->comment('Heure de début du créneau');

            $table->unsignedInteger('duration_minutes')
                  ->default(20)
                  ->comment('Durée du créneau en minutes');

            $table->unsignedInteger('max_slots')
                  ->default(20)
                  ->comment('Nombre max de consultations pour cette session');

            $table->unsignedInteger('slots_booked')
                  ->default(0)
                  ->comment('Nombre de réservations actuelles — incrémenté en transaction');

            $table->boolean('is_available')
                  ->default(true)
                  ->comment('false = créneau fermé manuellement par l\'agent');

            $table->enum('session_type', ['matinee', 'apres_midi', 'journee_entiere'])
                  ->default('matinee');

            $table->unsignedBigInteger('agenda_id')
                  ->nullable()
                  ->comment('Entrée agendas créée une fois le RDV confirmé');

            $table->timestamps();

            // Unicité du créneau par unité admin + date + heure
            $table->unique(
                ['unite_admin_id', 'slot_date', 'heure_debut'],
                'uq_slot_unite_date_heure'
            );

            // Index pour la recherche de créneaux disponibles (FN25, FA3.7, FN124)
            $table->index(
                ['unite_admin_id', 'slot_date', 'is_available'],
                'idx_slot_disponible'
            );

            $table->index(
                ['prestation_id', 'slot_date', 'is_available'],
                'idx_slot_prestation_date'
            );

            $table->foreign('unite_admin_id')
                  ->references('id')->on('unite_admins')
                  ->cascadeOnDelete();

            $table->foreign('prestation_id')
                  ->references('id')->on('prestations')
                  ->nullOnDelete();

            $table->foreign('agenda_id')
                  ->references('id')->on('agendas')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_slots');
    }
};
