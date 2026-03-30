<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WorkflowConsultationMedicaleSeeder
 *
 * Configure le workflow complet de la prestation "Visite médicale / Consultation DSSMST"
 * telle que documentée dans le flux C.2 (pages 83-92).
 *
 * Particularités vs flux 1 et 2 :
 *   - has_document_circuit = false  (pas de circuit paraphe/signature multi-acteurs)
 *   - Gestion de créneaux RDV via planning_slots
 *   - Documents produits avec is_auto_signed = true et is_selectable = true
 *   - Menu de choix post-consultation (condition_type = choix_sortie)
 *   - Statuts spécifiques au cycle médical
 *
 * Usage : php artisan db:seed --class=WorkflowConsultationMedicaleSeeder
 */
class WorkflowConsultationMedicaleSeeder extends Seeder
{
    public function run(): void
    {
        $sid = fn(string $sn) => DB::table('statuses')->where('short_name', $sn)->value('id');

        // ----------------------------------------------------------------
        // 1. PRESTATION
        // ----------------------------------------------------------------
        $prestationId = DB::table('prestations')
            ->where('slug', 'visite-medicale-dssmst')
            ->value('id');

        if (!$prestationId) {
            $prestationId = DB::table('prestations')->insertGetId([
                'code'                   => 'VMD',
                'name'                   => 'Visite médicale — DSSMST',
                'slug'                   => 'visite-medicale-dssmst',
                'need_meeting'           => true,   // flag existant — requiert un RDV
                'has_document_circuit'   => false,  // pas de circuit paraphe/signature
                'is_automatic_delivered' => false,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 2. ÉTAPES DU WORKFLOW
        // ----------------------------------------------------------------
        $etapesData = [
            // ── Phase RDV ──────────────────────────────────────────────────
            [
                'name'                  => 'Confirmation RDV — DSSMST',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 2,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            [
                'name'                  => 'Validation RDV — Requérant',
                'type'                  => 'depot',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 3,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // ── Phase consultation ─────────────────────────────────────────
            [
                'name'                  => 'Consultation médicale',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 1,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // ── Phase choix de sortie (branchement) ───────────────────────
            // Cette étape déclenche le menu de sélection de document
            [
                'name'                  => 'Choix de sortie consultation',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // ── Phase upload résultats ─────────────────────────────────────
            [
                'name'                  => 'Upload résultats examens — Requérant',
                'type'                  => 'depot',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 30,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            [
                'name'                  => 'Consultation résultats examens — DSSMST',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 5,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // ── Phase second RDV ──────────────────────────────────────────
            [
                'name'                  => 'Second RDV — En attente',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // ── Clôtures terminales ────────────────────────────────────────
            [
                'name'                  => 'Clôture — Certificat délivré',
                'type'                  => 'delivrance',
                'is_terminal'           => true,
                'allow_partial_save'    => false,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            [
                'name'                  => 'Clôture — Traitement prescrit',
                'type'                  => 'delivrance',
                'is_terminal'           => true,
                'allow_partial_save'    => false,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
        ];

        foreach ($etapesData as $e) {
            DB::table('etapes')->insertOrIgnore([
                ...$e,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $eid = fn(string $name) => DB::table('etapes')->where('name', $name)->value('id');

        // ----------------------------------------------------------------
        // 3. DOCUMENTS PRODUITS — 4 sorties de consultation + is_auto_signed
        // ----------------------------------------------------------------
        $docsProduits = [
            [
                'name'               => 'Bon d\'examens médicaux',
                'slug'               => 'bon_examens_medicaux',
                'type'               => 'attestation',
                'numero_prefix'      => 'BE',
                'template_key'       => 'documents.medical.bon_examens',
                'etape_edition'      => 'Choix de sortie consultation',
                'etape_delivrance'   => 'Clôture — Certificat délivré',
                'allow_correction'   => false,
                'is_auto_signed'     => true,   // signé automatiquement par le système
                'is_selectable'      => true,   // apparaît dans le menu post-consultation
                'order'              => 1,
            ],
            [
                'name'               => 'Certificat de repos',
                'slug'               => 'certificat_repos',
                'type'               => 'attestation',
                'numero_prefix'      => 'CR',
                'template_key'       => 'documents.medical.certificat_repos',
                'etape_edition'      => 'Choix de sortie consultation',
                'etape_delivrance'   => 'Clôture — Certificat délivré',
                'allow_correction'   => false,
                'is_auto_signed'     => true,
                'is_selectable'      => true,
                'order'              => 2,
            ],
            [
                'name'               => 'Ordonnance médicale',
                'slug'               => 'ordonnance_medicale',
                'type'               => 'attestation',
                'numero_prefix'      => 'ORD',
                'template_key'       => 'documents.medical.ordonnance',
                'etape_edition'      => 'Choix de sortie consultation',
                'etape_delivrance'   => 'Clôture — Traitement prescrit',
                'allow_correction'   => false,
                'is_auto_signed'     => true,
                'is_selectable'      => true,
                'order'              => 3,
            ],
            [
                'name'               => 'Certificat médical d\'aptitude',
                'slug'               => 'certificat_aptitude_medicale',
                'type'               => 'attestation',
                'numero_prefix'      => 'CAM',
                'template_key'       => 'documents.medical.certificat_aptitude',
                'etape_edition'      => 'Choix de sortie consultation',
                'etape_delivrance'   => 'Clôture — Certificat délivré',
                'allow_correction'   => false,
                'is_auto_signed'     => true,   // FN84 : signature+cachet médecin auto
                'is_selectable'      => true,
                'order'              => 4,
            ],
        ];

        $docIds = [];
        foreach ($docsProduits as $d) {
            $id = DB::table('etape_documents_produits')->insertGetId([
                'prestation_id'       => $prestationId,
                'name'                => $d['name'],
                'slug'                => $d['slug'],
                'type'                => $d['type'],
                'numero_prefix'       => $d['numero_prefix'],
                'template_key'        => $d['template_key'],
                'etape_edition_id'    => $eid($d['etape_edition']),
                'etape_delivrance_id' => $eid($d['etape_delivrance']),
                'allow_correction'    => $d['allow_correction'],
                'is_auto_signed'      => $d['is_auto_signed'],
                'is_selectable'       => $d['is_selectable'],
                'order'               => $d['order'],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
            $docIds[$d['slug']] = $id;
        }

        // ----------------------------------------------------------------
        // 4. CIRCUITS DE SIGNATURE — tous courts (1 seule étape : edition auto)
        //    is_auto_signed = true → le système signe sans acteur supplémentaire
        // ----------------------------------------------------------------
        foreach ($docsProduits as $d) {
            DB::table('document_circuit_etapes')->insert([
                'doc_produit_id'       => $docIds[$d['slug']],
                'unite_admin_id'       => null,    // géré par le système
                'role_name'            => 'DSSMST',
                'action_type'          => 'edition',
                'status_after'         => 'complet',
                'requete_status_after' => match($d['slug']) {
                    'certificat_aptitude_medicale' => 'valide',
                    'ordonnance_medicale'           => 'traitement_envoye',
                    default                         => 'en_traitement',
                },
                'is_blocking'          => true,
                'order'                => 1,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 5. TRANSITIONS DU WORKFLOW
        // ----------------------------------------------------------------
        $transitions = [
            // ── Gestion RDV ────────────────────────────────────────────────
            // Dépôt demande → Confirmation RDV par DSSMST
            [
                'from'         => null,  // point d'entrée — étape initiale
                'to'           => 'Confirmation RDV — DSSMST',
                'condition'    => 'auto',
                'status'       => 'en_attente',
                'notify_req'   => false,
                'notify_agent' => true,
                'order'        => 1,
            ],
            // DSSMST confirme → Requérant valide
            [
                'from'         => 'Confirmation RDV — DSSMST',
                'to'           => 'Validation RDV — Requérant',
                'condition'    => 'validation',
                'status'       => 'rdv_confirme',
                'notify_req'   => true,   // FN15 : mail confirmation RDV
                'notify_agent' => true,   // FN16 : mail DSSMST
                'order'        => 1,
            ],
            // FA3 : DSSMST annule et reprogramme
            [
                'from'         => 'Confirmation RDV — DSSMST',
                'to'           => 'Validation RDV — Requérant',
                'condition'    => 'rejet',
                'status'       => 'rdv_reprogramme',
                'notify_req'   => true,   // FA3.9 : mail nouveau RDV
                'notify_agent' => false,
                'order'        => 2,
            ],
            // Requérant annule et choisit nouveau créneau → retour en attente
            [
                'from'         => 'Validation RDV — Requérant',
                'to'           => 'Confirmation RDV — DSSMST',
                'condition'    => 'rejet',
                'status'       => 'rdv_reprogramme',
                'notify_req'   => true,   // FN27-28 : mail récap nouveau RDV
                'notify_agent' => false,
                'order'        => 1,
            ],
            // Requérant valide → Consultation médicale du jour
            [
                'from'         => 'Validation RDV — Requérant',
                'to'           => 'Consultation médicale',
                'condition'    => 'validation',
                'status'       => 'rdv_confirme',
                'notify_req'   => false,
                'notify_agent' => false,
                'order'        => 2,
            ],
            // ── Consultation ───────────────────────────────────────────────
            // Saisie constantes + finalisation → menu de choix
            [
                'from'         => 'Consultation médicale',
                'to'           => 'Choix de sortie consultation',
                'condition'    => 'choix_sortie',   // déclenche le menu
                'status'       => 'en_traitement',
                'notify_req'   => false,
                'notify_agent' => false,
                'order'        => 1,
            ],
            // ── Sorties vers examens → upload résultats ────────────────────
            [
                'from'         => 'Choix de sortie consultation',
                'to'           => 'Upload résultats examens — Requérant',
                'condition'    => 'validation',   // agent choisit "bon d'examens"
                'status'       => 'en_traitement',
                'notify_req'   => true,   // FN49 : statut en traitement
                'notify_agent' => false,
                'order'        => 1,
            ],
            // Requérant uploade résultats → Consultation résultats DSSMST
            [
                'from'         => 'Upload résultats examens — Requérant',
                'to'           => 'Consultation résultats examens — DSSMST',
                'condition'    => 'validation',
                'status'       => 'resultats_rendus',
                'notify_req'   => false,
                'notify_agent' => true,   // FN99 : résultats accessibles DSSMST
                'order'        => 1,
            ],
            // DSSMST consulte résultats → second menu de choix
            [
                'from'         => 'Consultation résultats examens — DSSMST',
                'to'           => 'Choix de sortie consultation',
                'condition'    => 'choix_sortie',
                'status'       => 'resultats_rendus',
                'notify_req'   => false,
                'notify_agent' => false,
                'order'        => 1,
            ],
            // DSSMST demande second RDV physique (FN123/FN116)
            [
                'from'         => 'Consultation résultats examens — DSSMST',
                'to'           => 'Second RDV — En attente',
                'condition'    => 'validation',
                'status'       => 'en_attente_2e_consultation',
                'notify_req'   => true,   // FN126 : mail date nouveau RDV
                'notify_agent' => false,
                'order'        => 2,
            ],
            // Second RDV → Consultation médicale (boucle)
            [
                'from'         => 'Second RDV — En attente',
                'to'           => 'Consultation médicale',
                'condition'    => 'auto',
                'status'       => 'en_attente_2e_consultation',
                'notify_req'   => false,
                'notify_agent' => false,
                'order'        => 1,
            ],
            // ── Sorties terminales ─────────────────────────────────────────
            // Certificat délivré → Clôture
            [
                'from'         => 'Choix de sortie consultation',
                'to'           => 'Clôture — Certificat délivré',
                'condition'    => 'auto',
                'status'       => 'valide',
                'notify_req'   => true,   // FN86 : mail + envoi certificat
                'notify_agent' => false,
                'order'        => 2,
            ],
            // Traitement prescrit → Clôture traitement
            [
                'from'         => 'Choix de sortie consultation',
                'to'           => 'Clôture — Traitement prescrit',
                'condition'    => 'auto',
                'status'       => 'traitement_envoye',
                'notify_req'   => true,   // FN141 : mail traitement à suivre
                'notify_agent' => false,
                'order'        => 3,
            ],
        ];

        foreach ($transitions as $t) {
            DB::table('workflow_transitions')->insertGetId([
                'prestation_id'    => $prestationId,
                'etape_from_id'    => $t['from'] ? $eid($t['from']) : $eid('Confirmation RDV — DSSMST'),
                'etape_to_id'      => $t['to'] ? $eid($t['to']) : null,
                'condition_type'   => $t['condition'],
                'status_result_id' => $sid($t['status']),
                'notify_requérant' => $t['notify_req'],
                'notify_agent'     => $t['notify_agent'],
                'is_active'        => true,
                'order'            => $t['order'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 6. DOCUMENTS REQUIS — pièces fournies par le requérant
        // ----------------------------------------------------------------
        $docsRequerant = [
            ['etape' => 'Validation RDV — Requérant',
             'name'  => 'Pièce d\'identité / NPI',
             'slug'  => 'identite_npi', 'req' => true, 'order' => 1],
            ['etape' => 'Upload résultats examens — Requérant',
             'name'  => 'Résultats d\'examens médicaux',
             'slug'  => 'resultats_examens', 'req' => true, 'order' => 1],
            ['etape' => 'Upload résultats examens — Requérant',
             'name'  => 'Compte-rendu médical antérieur',
             'slug'  => 'compte_rendu_anterieur', 'req' => false, 'order' => 2],
        ];

        foreach ($docsRequerant as $d) {
            DB::table('etape_documents')->insertOrIgnore([
                'prestation_id'       => $prestationId,
                'etape_id'            => $eid($d['etape']),
                'name'                => $d['name'],
                'slug'                => $d['slug'],
                'is_required'         => $d['req'],
                'accepted_mime_types' => json_encode([
                    'application/pdf', 'image/jpeg', 'image/png',
                ]),
                'max_size_kb'  => 5120,
                'order'        => $d['order'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 7. NOTIFICATIONS CLÉS
        // ----------------------------------------------------------------
        // Récupérer les transitions utiles pour les notifications
        $getTransId = fn(string $from, string $to, string $cond) =>
            DB::table('workflow_transitions')
                ->where('prestation_id', $prestationId)
                ->where('etape_from_id', $eid($from))
                ->where('etape_to_id', $eid($to))
                ->where('condition_type', $cond)
                ->value('id');

        $notifications = [
            // Confirmation RDV → mail requérant (FN15) + mail DSSMST (FN16)
            [
                'transition_from'  => 'Confirmation RDV — DSSMST',
                'transition_to'    => 'Validation RDV — Requérant',
                'condition'        => 'validation',
                'channel'          => 'email',
                'recipient'        => 'requérant',
                'template'         => 'emails.medical.rdv_confirme_requérant',
            ],
            [
                'transition_from'  => 'Confirmation RDV — DSSMST',
                'transition_to'    => 'Validation RDV — Requérant',
                'condition'        => 'validation',
                'channel'          => 'email',
                'recipient'        => 'agent',
                'template'         => 'emails.medical.rdv_confirme_dssmst',
            ],
            // Résultats uploadés → notification DSSMST (FN99)
            [
                'transition_from'  => 'Upload résultats examens — Requérant',
                'transition_to'    => 'Consultation résultats examens — DSSMST',
                'condition'        => 'validation',
                'channel'          => 'email',
                'recipient'        => 'agent',
                'template'         => 'emails.medical.resultats_disponibles',
            ],
        ];

        foreach ($notifications as $n) {
            $tid = $getTransId($n['transition_from'], $n['transition_to'], $n['condition']);
            if (!$tid) continue;

            DB::table('etape_notifications')->insert([
                'workflow_transition_id' => $tid,
                'channel'                => $n['channel'],
                'recipient_type'         => $n['recipient'],
                'template_key'           => $n['template'],
                'extra_data'             => null,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // RAPPORT FINAL
        // ----------------------------------------------------------------
        $this->command->info('Workflow "Visite médicale DSSMST" configuré avec succès.');
        $this->command->table(
            ['Élément', 'Quantité'],
            [
                ['Étapes créées',               count($etapesData)],
                ['Transitions créées',           count($transitions)],
                ['Documents produits (sélect.)', count($docsProduits)],
                ['Circuits (1 étape auto)',       count($docsProduits)],
                ['Docs requis requérant',         count($docsRequerant)],
                ['Notifications configurées',     count($notifications)],
                ['Nouveaux statuts (migration 012)', 6],
            ]
        );
    }
}
