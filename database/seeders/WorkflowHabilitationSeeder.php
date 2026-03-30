<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WorkflowHabilitationSeeder
 *
 * Configure en base le workflow complet de la prestation
 * "Habilitation centre de formation" telle que documentée dans le flux C.1/C.2.
 *
 * À exécuter UNE SEULE FOIS après les 6 migrations.
 * Modifier ce seeder pour ajouter une nouvelle prestation
 * sans toucher au code applicatif.
 *
 * Usage : php artisan db:seed --class=WorkflowHabilitationSeeder
 */
class WorkflowHabilitationSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. STATUTS
        // ----------------------------------------------------------------
        $statuses = [
            ['short_name' => 'en_saisie',        'name' => 'En cours de saisie'],
            ['short_name' => 'en_attente',        'name' => 'En attente de traitement'],
            ['short_name' => 'en_verification',   'name' => 'En vérification'],
            ['short_name' => 'transmis',          'name' => 'Transmis au comité'],
            ['short_name' => 'etudie',            'name' => 'Étudié par le comité'],
            ['short_name' => 'valide',            'name' => 'Validé par le ministre'],
            ['short_name' => 'rejete',            'name' => 'Rejeté — complément possible'],
            ['short_name' => 'rejete_clos',       'name' => 'Rejeté et clôturé'],
            ['short_name' => 'repertoire_edite',  'name' => 'Répertoire édité'],
            ['short_name' => 'cloture',           'name' => 'Clôturé'],
        ];

        foreach ($statuses as $s) {
            DB::table('statuses')->insertOrIgnore([
                ...$s,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sid = fn(string $sn) => DB::table('statuses')->where('short_name', $sn)->value('id');

        // ----------------------------------------------------------------
        // 2. ÉTAPES (dans l'ordre du parcours)
        // ----------------------------------------------------------------
        $etapesData = [
            [
                'name'               => 'Dépôt de la demande',
                'type'               => 'depot',
                'is_terminal'        => false,
                'allow_partial_save' => true,   // FA2 : sauvegarde sans finaliser
                'sla_days'           => null,
            ],
            [
                'name'               => 'Prise en charge — Service des études',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 30,
            ],
            [
                'name'               => 'Transmission au comité',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 7,
            ],
            [
                'name'               => 'Délibération du comité',
                'type'               => 'commission',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 14,
            ],
            [
                'name'               => 'Signature du ministre',
                'type'               => 'commission',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 7,
            ],
            [
                'name'               => 'Édition du répertoire',
                'type'               => 'delivrance',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 5,
            ],
            [
                'name'               => 'Clôture',
                'type'               => 'delivrance',
                'is_terminal'        => true,
                'allow_partial_save' => false,
                'sla_days'           => null,
            ],
            // Étapes terminales alternatives (rejets)
            [
                'name'               => 'Complément requis — Requérant',
                'type'               => 'depot',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 30,  // délai pour compléter avant clôture auto
            ],
            [
                'name'               => 'Rejet définitif',
                'type'               => 'traitement',
                'is_terminal'        => true,
                'allow_partial_save' => false,
                'sla_days'           => null,
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
        // 3. PRESTATION (récupérer ou créer)
        // ----------------------------------------------------------------
        $prestationId = DB::table('prestations')
            ->where('slug', 'habilitation-centre-formation')
            ->value('id');

        if (!$prestationId) {
            $prestationId = DB::table('prestations')->insertGetId([
                'code'       => 'HCF',
                'name'       => 'Habilitation centre de formation',
                'slug'       => 'habilitation-centre-formation',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 4. TRANSITIONS DU WORKFLOW
        // Schéma :
        //   dépôt ──auto──► prise_en_charge ──validation──► transmission_comité
        //                        └──rejet──► complément ──complement──► prise_en_charge
        //   transmission_comité ──validation──► délibération_comité
        //   délibération_comité ──validation──► signature_ministre
        //                              └──cloture──► rejet_définitif
        //   signature_ministre ──signature──► édition_répertoire ──auto──► clôture
        // ----------------------------------------------------------------
        $transitions = [
            // Dépôt → Prise en charge
            [
                'etape_from'       => 'Dépôt de la demande',
                'etape_to'         => 'Prise en charge — Service des études',
                'condition_type'   => 'auto',
                'status_result'    => 'en_attente',
                'notify_requérant' => true,   // récépissé de dépôt (FN20)
                'notify_agent'     => true,
                'order'            => 1,
            ],
            // Prise en charge → Transmission comité (dossier complet)
            [
                'etape_from'       => 'Prise en charge — Service des études',
                'etape_to'         => 'Transmission au comité',
                'condition_type'   => 'validation',
                'status_result'    => 'en_verification',
                'notify_requérant' => false,
                'notify_agent'     => true,
                'order'            => 1,
            ],
            // Prise en charge → Complément requis (FA1 : dossier incomplet)
            [
                'etape_from'       => 'Prise en charge — Service des études',
                'etape_to'         => 'Complément requis — Requérant',
                'condition_type'   => 'rejet',
                'status_result'    => 'rejete',
                'notify_requérant' => true,   // FA1.2 : notification rejet
                'notify_agent'     => false,
                'order'            => 2,
            ],
            // Complément → retour Prise en charge (FA1.3 → FA1.9)
            [
                'etape_from'       => 'Complément requis — Requérant',
                'etape_to'         => 'Prise en charge — Service des études',
                'condition_type'   => 'complement',
                'status_result'    => 'en_verification',
                'notify_requérant' => false,
                'notify_agent'     => true,   // FA1.10 : notification service études
                'order'            => 1,
            ],
            // Transmission → Délibération comité
            [
                'etape_from'       => 'Transmission au comité',
                'etape_to'         => 'Délibération du comité',
                'condition_type'   => 'validation',
                'status_result'    => 'transmis',
                'notify_requérant' => false,
                'notify_agent'     => true,
                'order'            => 1,
            ],
            // Délibération → Signature ministre (décision favorable)
            [
                'etape_from'       => 'Délibération du comité',
                'etape_to'         => 'Signature du ministre',
                'condition_type'   => 'validation',
                'status_result'    => 'etudie',
                'notify_requérant' => false,
                'notify_agent'     => true,
                'order'            => 1,
            ],
            // Délibération → Rejet définitif (FA2 comité : rejeté-clos)
            [
                'etape_from'       => 'Délibération du comité',
                'etape_to'         => 'Rejet définitif',
                'condition_type'   => 'cloture',
                'status_result'    => 'rejete_clos',
                'notify_requérant' => true,
                'notify_agent'     => false,
                'order'            => 2,
            ],
            // Signature → Édition répertoire (FN31→FN35)
            [
                'etape_from'       => 'Signature du ministre',
                'etape_to'         => 'Édition du répertoire',
                'condition_type'   => 'signature',
                'status_result'    => 'valide',
                'notify_requérant' => true,   // FN38 : invitation à télécharger
                'notify_agent'     => true,
                'order'            => 1,
            ],
            // Édition répertoire → Clôture
            [
                'etape_from'       => 'Édition du répertoire',
                'etape_to'         => 'Clôture',
                'condition_type'   => 'validation',
                'status_result'    => 'repertoire_edite',
                'notify_requérant' => false,
                'notify_agent'     => false,
                'order'            => 1,
            ],
        ];

        foreach ($transitions as $t) {
            $transitionId = DB::table('workflow_transitions')->insertGetId([
                'prestation_id'    => $prestationId,
                'etape_from_id'    => $eid($t['etape_from']),
                'etape_to_id'      => $eid($t['etape_to']),
                'condition_type'   => $t['condition_type'],
                'status_result_id' => $sid($t['status_result']),
                'notify_requérant' => $t['notify_requérant'],
                'notify_agent'     => $t['notify_agent'],
                'is_active'        => true,
                'order'            => $t['order'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 5. DOCUMENTS REQUIS PAR ÉTAPE
        // ----------------------------------------------------------------
        $documents = [
            // Étape dépôt
            ['etape' => 'Dépôt de la demande', 'name' => 'Statuts de l\'établissement',    'slug' => 'statuts',         'required' => true,  'order' => 1],
            ['etape' => 'Dépôt de la demande', 'name' => 'RCCM',                            'slug' => 'rccm',            'required' => true,  'order' => 2],
            ['etape' => 'Dépôt de la demande', 'name' => 'IFU',                             'slug' => 'ifu',             'required' => true,  'order' => 3],
            ['etape' => 'Dépôt de la demande', 'name' => 'Plan de localisation',            'slug' => 'plan_localisation','required' => true, 'order' => 4],
            ['etape' => 'Dépôt de la demande', 'name' => 'Programme de formations',         'slug' => 'prog_formations', 'required' => true,  'order' => 5],
            ['etape' => 'Dépôt de la demande', 'name' => 'CV des formateurs',               'slug' => 'cv_formateurs',   'required' => false, 'order' => 6],
            // Étape transmission
            ['etape' => 'Transmission au comité', 'name' => 'Synthèse d\'étude du dossier', 'slug' => 'synthese_etude',  'required' => true,  'order' => 1],
            ['etape' => 'Transmission au comité', 'name' => 'Rapport de visite de site',    'slug' => 'rapport_visite',  'required' => true,  'order' => 2],
        ];

        foreach ($documents as $d) {
            DB::table('etape_documents')->insertOrIgnore([
                'prestation_id'     => $prestationId,
                'etape_id'          => $eid($d['etape']),
                'name'              => $d['name'],
                'slug'              => $d['slug'],
                'is_required'       => $d['required'],
                'accepted_mime_types' => json_encode(['application/pdf', 'image/jpeg', 'image/png']),
                'max_size_kb'       => 5120,
                'order'             => $d['order'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $this->command->info('Workflow "Habilitation centre de formation" configuré avec succès.');
        $this->command->table(
            ['Élément', 'Quantité'],
            [
                ['Statuts créés',     count($statuses)],
                ['Étapes créées',     count($etapesData)],
                ['Transitions créées', count($transitions)],
                ['Documents configurés', count($documents)],
            ]
        );
    }
}
