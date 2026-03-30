<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 4/6 — Création de la table `etape_notifications`
 *
 * Chaque transition de workflow peut déclencher une ou plusieurs notifications.
 * Cette table configure CE QUI est envoyé, À QUI, et VIA QUEL CANAL,
 * pour chaque transition — sans toucher au code.
 *
 * Exemples tirés du flux documenté :
 *
 *   transition "dépôt → en_attente"
 *     → email requérant  : template "depot_confirmation"  (récépissé de dépôt FN20)
 *
 *   transition "validation → en_vérification"
 *     → email agent      : template "dossier_pris_en_charge"
 *
 *   transition "rejet → rejeté"
 *     → email requérant  : template "dossier_rejet"  (FA1.2)
 *     → sms   requérant  : template "dossier_rejet_sms"
 *
 *   transition "signature → validé"
 *     → email requérant  : template "habilitation_accordee"  (FN38)
 *
 * template_key : clé utilisée par le système de mail/SMS pour charger
 *   le bon template Blade/contenu SMS (ex: "emails.depot_confirmation")
 *
 * extra_data : JSON de variables supplémentaires injectées dans le template
 *   ex: {"include_receipt": true, "include_decision_link": true}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etape_notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('workflow_transition_id')
                  ->comment('Transition qui déclenche cette notification');

            $table->enum('channel', ['email', 'sms', 'whatsapp'])
                  ->default('email')
                  ->comment('Canal d\'envoi');

            $table->enum('recipient_type', ['requérant', 'agent', 'ministre', 'unite_admin'])
                  ->comment('Type de destinataire');

            $table->string('template_key')
                  ->comment('Clé du template (ex: emails.depot_confirmation)');

            $table->json('extra_data')
                  ->nullable()
                  ->comment('Variables additionnelles injectées dans le template');

            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Permet de désactiver sans supprimer');

            $table->timestamps();

            $table->index('workflow_transition_id', 'idx_notif_transition');

            $table->foreign('workflow_transition_id')
                  ->references('id')->on('workflow_transitions')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etape_notifications');
    }
};
