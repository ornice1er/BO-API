<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WorkflowAgrementSeeder
 *
 * Configure en base le workflow complet de la prestation "Agrément"
 * telle que documentée dans le flux C.2 (pages 51-59).
 *
 * Ce seeder démontre l'extensibilité du modèle :
 * même moteur, configuration différente, aucun code PHP modifié.
 *
 * Circuit documentaire :
 *   Projet de lettre    : DSSMST (édition) → DGT (paraphe) → Ministre (signature)
 *                         → DNSP (prévalidation)
 *   Décision d'agrément : DSSMST (édition) → DGT (paraphe) → Ministre (signature)
 *
 * Usage : php artisan db:seed --class=WorkflowAgrementSeeder
 */
class WorkflowAgrementSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. STATUTS SPÉCIFIQUES AU FLUX AGRÉMENT
        //    (les statuts génériques existent déjà via WorkflowHabilitationSeeder)
        // ----------------------------------------------------------------
        $nouveauxStatuts = [
            // Statuts génériques (insertOrIgnore si déjà présents)
            ['short_name' => 'en_attente',          'name' => 'En attente'],
            ['short_name' => 'en_verification',     'name' => 'En vérification'],
            ['short_name' => 'rejete',              'name' => 'Rejeté'],
            ['short_name' => 'rejete_clos',         'name' => 'Rejeté — Clôturé'],
            ['short_name' => 'valide',              'name' => 'Validé'],
            // Statuts spécifiques agrément
            ['short_name' => 'envoye',              'name' => 'Envoyé — en attente DGT'],
            ['short_name' => 'en_attente_signature', 'name' => 'En attente de signature'],
            ['short_name' => 'paraphe',             'name' => 'Paraphé'],
            ['short_name' => 'prevalide',           'name' => 'Pré-validé par la DNSP'],
        ];

        foreach ($nouveauxStatuts as $s) {
            DB::table('statuses')->insertOrIgnore([
                ...$s,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sid = fn(string $sn) => DB::table('statuses')->where('short_name', $sn)->value('id');

        // ----------------------------------------------------------------
        // 2. ÉTAPES DU WORKFLOW AGRÉMENT
        // ----------------------------------------------------------------
        $etapesData = [
            // --- Phase dépôt (identique habilitation, réutilisable) ---
            [
                'name'                  => 'Dépôt demande agrément',
                'type'                  => 'depot',
                'is_terminal'           => false,
                'allow_partial_save'    => true,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Phase traitement DSSMST ---
            [
                'name'                  => 'Prise en charge DSSMST',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 15,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // Étape d'édition du projet de lettre (DSSMST génère le doc)
            [
                'name'                  => 'Édition projet de lettre',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 5,
                'produces_document'     => true,
                'document_template_key' => 'documents.agrement.projet_lettre',
            ],
            // --- Phase paraphe DGT ---
            [
                'name'                  => 'Paraphe projet de lettre — DGT',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 3,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Phase signature Ministre (lettre) ---
            [
                'name'                  => 'Signature projet de lettre — Ministre',
                'type'                  => 'commission',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 5,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Phase prévalidation DNSP ---
            [
                'name'                  => 'Prévalidation DNSP',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 10,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Phase édition décision d'agrément (DSSMST) ---
            [
                'name'                  => 'Édition décision d\'agrément',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 5,
                'produces_document'     => true,
                'document_template_key' => 'documents.agrement.decision_agrement',
            ],
            // --- Phase paraphe DGT (décision) ---
            [
                'name'                  => 'Paraphe décision d\'agrément — DGT',
                'type'                  => 'traitement',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 3,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Phase signature Ministre (décision) ---
            [
                'name'                  => 'Signature décision d\'agrément — Ministre',
                'type'                  => 'commission',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 5,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Clôture ---
            [
                'name'                  => 'Clôture agrément',
                'type'                  => 'delivrance',
                'is_terminal'           => true,
                'allow_partial_save'    => false,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            // --- Étapes alternatives ---
            [
                'name'                  => 'Complément requis — Agrément',
                'type'                  => 'depot',
                'is_terminal'           => false,
                'allow_partial_save'    => false,
                'sla_days'              => 30,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
            [
                'name'                  => 'Rejet DNSP — Agrément',
                'type'                  => 'traitement',
                'is_terminal'           => true,
                'allow_partial_save'    => false,
                'sla_days'              => null,
                'produces_document'     => false,
                'document_template_key' => null,
            ],
        ];

        foreach ($etapesData as $e) {
            DB::table('etapes')->insertOrIgnore([
                'name'                  => $e['name'],
                'type'                  => $e['type'],
                'is_terminal'           => $e['is_terminal'],
                'allow_partial_save'    => $e['allow_partial_save'],
                'sla_days'              => $e['sla_days'],
                'produces_document'     => $e['produces_document'],
                'document_template_key' => $e['document_template_key'],
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        $eid = fn(string $name) => DB::table('etapes')->where('name', $name)->value('id');

        // ----------------------------------------------------------------
        // 3. PRESTATION
        // ----------------------------------------------------------------
        $prestationId = DB::table('prestations')
            ->where('slug', 'agrement')
            ->value('id');

        if (!$prestationId) {
            $prestationId = DB::table('prestations')->insertGetId([
                'code'                 => 'AGR',
                'name'                 => 'Agrément',
                'slug'                 => 'agrement',
                'has_document_circuit' => true,  // active le sous-système
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        } else {
            DB::table('prestations')
                ->where('id', $prestationId)
                ->update(['has_document_circuit' => true]);
        }

        // ----------------------------------------------------------------
        // 4. TRANSITIONS DU WORKFLOW
        // ----------------------------------------------------------------
        $transitions = [
            [
                'from'           => 'Dépôt demande agrément',
                'to'             => 'Prise en charge DSSMST',
                'condition'      => 'auto',
                'status'         => 'en_attente',
                'notify_req'     => true,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // Prise en charge → Édition projet de lettre
            [
                'from'           => 'Prise en charge DSSMST',
                'to'             => 'Édition projet de lettre',
                'condition'      => 'validation',
                'status'         => 'en_verification',
                'notify_req'     => false,
                'notify_agent'   => false,
                'order'          => 1,
            ],
            // FA2 : rejet DSSMST → complément
            [
                'from'           => 'Prise en charge DSSMST',
                'to'             => 'Complément requis — Agrément',
                'condition'      => 'rejet',
                'status'         => 'rejete',
                'notify_req'     => true,
                'notify_agent'   => false,
                'order'          => 2,
            ],
            // Complément → retour prise en charge
            [
                'from'           => 'Complément requis — Agrément',
                'to'             => 'Prise en charge DSSMST',
                'condition'      => 'complement',
                'status'         => 'en_verification',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // Édition lettre → Paraphe DGT (doc envoyé = statut "envoyé" FN15)
            [
                'from'           => 'Édition projet de lettre',
                'to'             => 'Paraphe projet de lettre — DGT',
                'condition'      => 'validation',
                'status'         => 'envoye',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // DGT paraphe lettre → Signature Ministre
            [
                'from'           => 'Paraphe projet de lettre — DGT',
                'to'             => 'Signature projet de lettre — Ministre',
                'condition'      => 'paraphe',
                'status'         => 'paraphe',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // Ministre signe lettre → Prévalidation DNSP (FN33)
            [
                'from'           => 'Signature projet de lettre — Ministre',
                'to'             => 'Prévalidation DNSP',
                'condition'      => 'signature',
                'status'         => 'en_attente_signature',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // DNSP pré-valide → Édition décision (FN46→FN52)
            [
                'from'           => 'Prévalidation DNSP',
                'to'             => 'Édition décision d\'agrément',
                'condition'      => 'prevalidation',
                'status'         => 'prevalide',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // FA4 : DNSP rejette → rejet définitif
            [
                'from'           => 'Prévalidation DNSP',
                'to'             => 'Rejet DNSP — Agrément',
                'condition'      => 'rejet',
                'status'         => 'rejete_clos',
                'notify_req'     => true,
                'notify_agent'   => false,
                'order'          => 2,
            ],
            // Édition décision → Paraphe DGT (décision)
            [
                'from'           => 'Édition décision d\'agrément',
                'to'             => 'Paraphe décision d\'agrément — DGT',
                'condition'      => 'validation',
                'status'         => 'en_attente_signature',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // DGT paraphe décision → Signature Ministre (décision)
            [
                'from'           => 'Paraphe décision d\'agrément — DGT',
                'to'             => 'Signature décision d\'agrément — Ministre',
                'condition'      => 'paraphe',
                'status'         => 'paraphe',
                'notify_req'     => false,
                'notify_agent'   => true,
                'order'          => 1,
            ],
            // Ministre signe décision → Clôture (FN85→FN88→FN89)
            [
                'from'           => 'Signature décision d\'agrément — Ministre',
                'to'             => 'Clôture agrément',
                'condition'      => 'signature',
                'status'         => 'valide',
                'notify_req'     => true,  // FN89 : mail invitation téléchargement
                'notify_agent'   => false,
                'order'          => 1,
            ],
        ];

        $transitionIds = [];
        foreach ($transitions as $t) {
            $id = DB::table('workflow_transitions')->insertGetId([
                'prestation_id'    => $prestationId,
                'etape_from_id'    => $eid($t['from']),
                'etape_to_id'      => $eid($t['to']),
                'condition_type'   => $t['condition'],
                'status_result_id' => $sid($t['status']),
                'notify_requérant' => $t['notify_req'],
                'notify_agent'     => $t['notify_agent'],
                'is_active'        => true,
                'order'            => $t['order'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $transitionIds["{$t['from']}→{$t['to']}"] = $id;
        }

        // ----------------------------------------------------------------
        // 5. DOCUMENTS PRODUITS — configuration des 2 artefacts
        // ----------------------------------------------------------------
        $projetLettreId = DB::table('etape_document_produits')->insertGetId([
            'prestation_id'       => $prestationId,
            'name'                => 'Projet de lettre d\'agrément',
            'slug'                => 'projet_lettre_agrement',
            'type'                => 'lettre',
            'numero_prefix'       => 'PL',
            'template_key'        => 'documents.agrement.projet_lettre',
            'etape_edition_id'    => $eid('Édition projet de lettre'),
            'etape_delivrance_id' => $eid('Clôture agrément'),
            'allow_correction'    => true,
            'order'               => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $decisionId = DB::table('etape_document_produits')->insertGetId([
            'prestation_id'       => $prestationId,
            'name'                => 'Décision d\'agrément',
            'slug'                => 'decision_agrement',
            'type'                => 'decision',
            'numero_prefix'       => 'DA',
            'template_key'        => 'documents.agrement.decision_agrement',
            'etape_edition_id'    => $eid('Édition décision d\'agrément'),
            'etape_delivrance_id' => $eid('Clôture agrément'),
            'allow_correction'    => true,
            'order'               => 2,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // ----------------------------------------------------------------
        // 6. CIRCUITS DE SIGNATURE — séquences acteurs par document
        // ----------------------------------------------------------------

        // Circuit Projet de lettre : DSSMST → DGT → Ministre → DNSP
        $circuitLettre = [
            ['role' => 'DSSMST',  'action' => 'edition',       'status_after' => 'en_edition',  'req_status' => null,       'blocking' => true,  'order' => 1],
            ['role' => 'DGT',     'action' => 'paraphe',        'status_after' => 'paraphé',     'req_status' => 'envoye',   'blocking' => true,  'order' => 2],
            ['role' => 'Ministre','action' => 'signature',      'status_after' => 'signé',       'req_status' => 'paraphe',  'blocking' => true,  'order' => 3],
            ['role' => 'DNSP',    'action' => 'prevalidation',  'status_after' => 'prévalidé',   'req_status' => 'prevalide','blocking' => true,  'order' => 4],
        ];

        foreach ($circuitLettre as $c) {
            DB::table('document_circuit_etapes')->insert([
                'doc_produit_id'       => $projetLettreId,
                'role_name'            => $c['role'],
                'action_type'          => $c['action'],
                'status_after'         => $c['status_after'],
                'requete_status_after' => $c['req_status'],
                'is_blocking'          => $c['blocking'],
                'order'                => $c['order'],
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // Circuit Décision d'agrément : DSSMST → DGT → Ministre
        $circuitDecision = [
            ['role' => 'DSSMST',  'action' => 'edition',  'status_after' => 'en_edition', 'req_status' => null,     'blocking' => true, 'order' => 1],
            ['role' => 'DGT',     'action' => 'paraphe',  'status_after' => 'paraphé',    'req_status' => null,     'blocking' => true, 'order' => 2],
            ['role' => 'Ministre','action' => 'signature', 'status_after' => 'signé',      'req_status' => 'valide', 'blocking' => true, 'order' => 3],
        ];

        foreach ($circuitDecision as $c) {
            DB::table('document_circuit_etapes')->insert([
                'doc_produit_id'       => $decisionId,
                'role_name'            => $c['role'],
                'action_type'          => $c['action'],
                'status_after'         => $c['status_after'],
                'requete_status_after' => $c['req_status'],
                'is_blocking'          => $c['blocking'],
                'order'                => $c['order'],
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 7. VISIBILITÉS PAR RÔLE (banettes normalisées)
        // ----------------------------------------------------------------
        $transId = fn(string $key) => $transitionIds[$key] ?? null;

        $visibilites = [
            // Après "Édition lettre → Paraphe DGT" (statut envoyé) — FN15
            ['transition' => 'Édition projet de lettre→Paraphe projet de lettre — DGT',
             'role' => 'DGT',    'read' => true,  'act' => true,  'scope' => 'document', 'doc_id' => $projetLettreId],
            ['transition' => 'Édition projet de lettre→Paraphe projet de lettre — DGT',
             'role' => 'DSSMST', 'read' => true,  'act' => false, 'scope' => 'document', 'doc_id' => $projetLettreId],

            // Après paraphe DGT → Signature Ministre — FN22/FN23
            ['transition' => 'Paraphe projet de lettre — DGT→Signature projet de lettre — Ministre',
             'role' => 'Ministre',           'read' => true, 'act' => true,  'scope' => 'document', 'doc_id' => $projetLettreId],
            ['transition' => 'Paraphe projet de lettre — DGT→Signature projet de lettre — Ministre',
             'role' => 'Secretariat_Ministre','read' => true, 'act' => false, 'scope' => 'document', 'doc_id' => $projetLettreId],
            ['transition' => 'Paraphe projet de lettre — DGT→Signature projet de lettre — Ministre',
             'role' => 'DGT',                'read' => true, 'act' => false, 'scope' => 'document', 'doc_id' => $projetLettreId],

            // Après signature Ministre lettre → DNSP prévalide — FN33
            ['transition' => 'Signature projet de lettre — Ministre→Prévalidation DNSP',
             'role' => 'DNSP',   'read' => true, 'act' => true,  'scope' => 'document', 'doc_id' => $projetLettreId],
            ['transition' => 'Signature projet de lettre — Ministre→Prévalidation DNSP',
             'role' => 'DSSMST', 'read' => true, 'act' => false, 'scope' => 'document', 'doc_id' => $projetLettreId],

            // Après DNSP → Édition décision (FN46)
            ['transition' => 'Prévalidation DNSP→Édition décision d\'agrément',
             'role' => 'DSSMST', 'read' => true, 'act' => true,  'scope' => 'document', 'doc_id' => $decisionId],

            // Après signature Ministre décision → Clôture — FN87
            ['transition' => 'Signature décision d\'agrément — Ministre→Clôture agrément',
             'role' => 'DSSMST', 'read' => true, 'act' => true,  'scope' => 'requete', 'doc_id' => null],
        ];

        foreach ($visibilites as $v) {
            $tid = $transId($v['transition']);
            if (!$tid) continue;
            DB::table('etape_visibilites')->insert([
                'workflow_transition_id' => $tid,
                'role_name'              => $v['role'],
                'can_read'               => $v['read'],
                'can_act'                => $v['act'],
                'scope_type'             => $v['scope'],
                'doc_produit_id'         => $v['doc_id'],
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 8. MOTIFS DE REJET (FA2 DSSMST + FA4 DNSP)
        // ----------------------------------------------------------------
        $motifs = [
            // FA2 — DSSMST
            ['etape' => 'Prise en charge DSSMST', 'code' => 'PIECES_MANQUANTES',  'libelle' => 'Pièces manquantes',             'complement' => true,  'final' => false, 'order' => 1],
            ['etape' => 'Prise en charge DSSMST', 'code' => 'CHAMP_MAL_RENSEIGNE','libelle' => 'Champ mal renseigné',            'complement' => true,  'final' => false, 'order' => 2],
            ['etape' => 'Prise en charge DSSMST', 'code' => 'DOSSIER_INCOMPLET',  'libelle' => 'Dossier incomplet',              'complement' => true,  'final' => false, 'order' => 3],
            ['etape' => 'Prise en charge DSSMST', 'code' => 'REJET_POUR_APPEL',   'libelle' => 'Rejet pour appel',               'complement' => false, 'final' => false, 'order' => 4],
            // FA4 — DNSP
            ['etape' => 'Prévalidation DNSP',     'code' => 'NON_CONFORME',        'libelle' => 'Demande non conforme',           'complement' => false, 'final' => true,  'order' => 1],
            ['etape' => 'Prévalidation DNSP',     'code' => 'INFOS_INCORRECTES',   'libelle' => 'Informations incorrectes',       'complement' => false, 'final' => true,  'order' => 2],
            ['etape' => 'Prévalidation DNSP',     'code' => 'NON_ELIGIBLE',        'libelle' => 'Établissement non éligible',     'complement' => false, 'final' => true,  'order' => 3],
        ];

        foreach ($motifs as $m) {
            DB::table('motifs_rejet')->insertOrIgnore([
                'prestation_id'   => $prestationId,
                'etape_id'        => $eid($m['etape']),
                'code'            => $m['code'],
                'libelle'         => $m['libelle'],
                'allow_complement'=> $m['complement'],
                'is_final'        => $m['final'],
                'is_active'       => true,
                'order'           => $m['order'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 9. DOCUMENTS REQUIS PAR ÉTAPE (pièces requérant)
        // ----------------------------------------------------------------
        $docsRequerant = [
            ['etape' => 'Dépôt demande agrément', 'name' => 'Demande manuscrite',         'slug' => 'demande_manuscrite',  'req' => true,  'order' => 1],
            ['etape' => 'Dépôt demande agrément', 'name' => 'Statuts de l\'entreprise',   'slug' => 'statuts',             'req' => true,  'order' => 2],
            ['etape' => 'Dépôt demande agrément', 'name' => 'RCCM',                       'slug' => 'rccm',                'req' => true,  'order' => 3],
            ['etape' => 'Dépôt demande agrément', 'name' => 'Casier judiciaire',           'slug' => 'casier_judiciaire',   'req' => true,  'order' => 4],
            ['etape' => 'Dépôt demande agrément', 'name' => 'Plan de localisation',       'slug' => 'plan_localisation',   'req' => true,  'order' => 5],
            ['etape' => 'Prise en charge DSSMST', 'name' => 'PV de visite de site',       'slug' => 'pv_visite_site',      'req' => true,  'order' => 1],
        ];

        foreach ($docsRequerant as $d) {
            DB::table('etape_documents')->insertOrIgnore([
                'prestation_id'      => $prestationId,
                'etape_id'           => $eid($d['etape']),
                'name'               => $d['name'],
                'slug'               => $d['slug'],
                'is_required'        => $d['req'],
                'accepted_mime_types'=> json_encode(['application/pdf', 'image/jpeg', 'image/png']),
                'max_size_kb'        => 5120,
                'order'              => $d['order'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // RAPPORT FINAL
        // ----------------------------------------------------------------
        $this->command->info('Workflow "Agrément" configuré avec succès.');
        $this->command->table(
            ['Élément', 'Quantité'],
            [
                ['Nouveaux statuts',         count($nouveauxStatuts)],
                ['Étapes créées',            count($etapesData)],
                ['Transitions créées',       count($transitions)],
                ['Documents produits',       2],
                ['Étapes circuit lettre',    count($circuitLettre)],
                ['Étapes circuit décision',  count($circuitDecision)],
                ['Règles visibilité',        count($visibilites)],
                ['Motifs de rejet',          count($motifs)],
                ['Docs requis requérant',    count($docsRequerant)],
            ]
        );
    }
}
