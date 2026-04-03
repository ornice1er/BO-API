<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WorkflowOuvertureEcoleSeeder
 *
 * Configure la prestation "Ouverture d'école" (et ses variantes création/extension).
 * Même pattern structurel que WorkflowAutorisationSeeder (diriger/enseigner) :
 *   - Même moteur de workflow
 *   - Même tables (etapes, workflow_transitions, motifs_rejet...)
 *   - Aucune migration supplémentaire requise
 *
 * DIFFÉRENCES vs flux 4a (diriger/enseigner) :
 *   1. Étape "Visite de site" intercalée entre DDEMP et CCD
 *   2. Acteur CCD (Comité Consultatif Départemental) au lieu de comité générique
 *   3. Statut "Téléchargé" après téléchargement DDEMP (UC006)
 *   4. Chaque sous-type (création, extension...) est une prestation distincte
 *      → même pattern que diriger ≠ enseigner
 *
 * Les étapes partagées avec le flux 4a (dépôt, prise en charge CS, correction,
 * finalisation, upload arrêté...) sont réutilisées via insertOrIgnore —
 * elles ne sont créées qu'une seule fois en base.
 *
 * Usage : php artisan db:seed --class=WorkflowOuvertureEcoleSeeder
 */
class WorkflowOuvertureEcoleSeeder extends Seeder
{
    public function run(): void
    {
        $sid = fn(string $sn) => DB::table('statuses')->where('short_name', $sn)->value('id');

        // ----------------------------------------------------------------
        // 1. NOUVEAUX STATUTS SPÉCIFIQUES AU FLUX 4b
        //    (les statuts communs avec flux 4a sont déjà insérés via migration 015)
        // ----------------------------------------------------------------
        $nouveauxStatuts = [
            ['short_name' => 'telecharge',              'name' => 'Téléchargé par l\'agent DDEMP'],
            ['short_name' => 'en_attente_visite_site',  'name' => 'En attente de la visite de site'],
            ['short_name' => 'en_cours_etude_ccd',      'name' => 'En cours d\'étude — CCD'],
        ];

        foreach ($nouveauxStatuts as $s) {
            DB::table('statuses')->insertOrIgnore([
                ...$s,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // 2. ÉTAPES — partagées + nouvelles spécifiques flux 4b
        //    insertOrIgnore : les étapes communes au flux 4a ne sont pas recréées
        // ----------------------------------------------------------------
        $etapesData = [
            // ── Communes avec flux 4a (réutilisées) ───────────────────────
            [
                'name'               => 'Dépôt demande autorisation',
                'type'               => 'depot',
                'is_terminal'        => false,
                'allow_partial_save' => true,
                'sla_days'           => null,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Prise en charge — Agent CS',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 5,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Correction demande — Usager',
                'type'               => 'depot',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 15,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Finalisation — DDEMP',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 10,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Upload arrêté — DDEMP',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 5,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Validation session — Ministre',
                'type'               => 'commission',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 7,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Génération autorisation — Système',
                'type'               => 'delivrance',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => null,
                'produces_document'  => true,
                'document_template_key' => 'documents.autorisation.autorisation_generique',
            ],
            [
                'name'               => 'Clôture autorisation',
                'type'               => 'delivrance',
                'is_terminal'        => true,
                'allow_partial_save' => false,
                'sla_days'           => null,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Rejet définitif autorisation',
                'type'               => 'traitement',
                'is_terminal'        => true,
                'allow_partial_save' => false,
                'sla_days'           => null,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            // ── Spécifiques flux 4b ────────────────────────────────────────
            [
                'name'               => 'Prise en charge — Agent DDEMP',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 5,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Visite de site — DDEMP',
                'type'               => 'traitement',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 15,
                'produces_document'  => false,
                'document_template_key' => null,
            ],
            [
                'name'               => 'Étude dossier — CCD',
                'type'               => 'commission',
                'is_terminal'        => false,
                'allow_partial_save' => false,
                'sla_days'           => 30,
                'produces_document'  => false,
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
        // 3. PRESTATIONS — une par sous-type de demande
        //    Même pattern que diriger/enseigner :
        //    chaque sous-type est une prestation avec son propre prestation_id
        // ----------------------------------------------------------------
        $prestations = [
            [
                'code' => 'OUV-ECO-CRE',
                'name' => 'Ouverture d\'école — Création',
                'slug' => 'ouverture-ecole-creation',
            ],
            [
                'code' => 'OUV-ECO-EXT',
                'name' => 'Ouverture d\'école — Extension',
                'slug' => 'ouverture-ecole-extension',
            ],
        ];

        foreach ($prestations as $pData) {
            $prestationId = DB::table('prestations')
                ->where('slug', $pData['slug'])
                ->value('id');

            if (!$prestationId) {
                $prestationId = DB::table('prestations')->insertGetId([
                    'code'                 => $pData['code'],
                    'name'                 => $pData['name'],
                    'slug'                 => $pData['slug'],
                    'has_document_circuit' => false,
                    'need_meeting'         => true,  // visite de site via agendas
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }

            // ----------------------------------------------------------------
            // 4. TRANSITIONS — circuit flux 4b
            //    CS → DDEMP → Visite site → CCD → Finalisation → Arrêté → Ministre
            // ----------------------------------------------------------------
            $transitions = [
                // Dépôt → Prise en charge CS
                [
                    'from'         => 'Dépôt demande autorisation',
                    'to'           => 'Prise en charge — Agent CS',
                    'condition'    => 'auto',
                    'status'       => 'en_attente',
                    'notify_req'   => false,
                    'notify_agent' => true,
                    'order'        => 1,
                ],
                // CS prend en charge → notifie → DDEMP prend en charge (UC003→UC004)
                [
                    'from'         => 'Prise en charge — Agent CS',
                    'to'           => 'Prise en charge — Agent DDEMP',
                    'condition'    => 'validation',
                    'status'       => 'pris_en_charge',
                    'notify_req'   => true,   // UC004 : CS notifie l'usager
                    'notify_agent' => true,
                    'order'        => 1,
                ],
                // CS rejette → correction usager (UC004 scénario 5.a)
                [
                    'from'         => 'Prise en charge — Agent CS',
                    'to'           => 'Correction demande — Usager',
                    'condition'    => 'rejet',
                    'status'       => 'rejete',
                    'notify_req'   => true,
                    'notify_agent' => false,
                    'order'        => 2,
                ],
                // DDEMP télécharge dossier → statut Téléchargé (UC006)
                [
                    'from'         => 'Prise en charge — Agent DDEMP',
                    'to'           => 'Visite de site — DDEMP',
                    'condition'    => 'validation',
                    'status'       => 'telecharge',
                    'notify_req'   => true,   // UC006 : notif usager + espace suivi
                    'notify_agent' => false,
                    'order'        => 1,
                ],
                // DDEMP programme visite → En attente visite (UC007)
                [
                    'from'         => 'Visite de site — DDEMP',
                    'to'           => 'Étude dossier — CCD',
                    'condition'    => 'validation',
                    'status'       => 'en_attente_visite_site',
                    'notify_req'   => true,   // UC007 : notif mail + espace suivi
                    'notify_agent' => false,
                    'order'        => 1,
                ],
                // CCD valide → finalisation DDEMP (UC008)
                [
                    'from'         => 'Étude dossier — CCD',
                    'to'           => 'Finalisation — DDEMP',
                    'condition'    => 'validation',
                    'status'       => 'en_attente_finalisation',
                    'notify_req'   => false,
                    'notify_agent' => false,
                    'order'        => 1,
                ],
                // CCD rejette → correction usager (UC008 scénario 5.a)
                [
                    'from'         => 'Étude dossier — CCD',
                    'to'           => 'Correction demande — Usager',
                    'condition'    => 'rejet',
                    'status'       => 'rejete',
                    'notify_req'   => true,
                    'notify_agent' => false,
                    'order'        => 2,
                ],
                // Usager corrige → retour DDEMP (UC005/UC009)
                [
                    'from'         => 'Correction demande — Usager',
                    'to'           => 'Prise en charge — Agent DDEMP',
                    'condition'    => 'complement',
                    'status'       => 'corrige',
                    'notify_req'   => false,
                    'notify_agent' => true,
                    'order'        => 1,
                ],
                // DDEMP finalise → validé (UC011)
                [
                    'from'         => 'Finalisation — DDEMP',
                    'to'           => 'Upload arrêté — DDEMP',
                    'condition'    => 'validation',
                    'status'       => 'valide',
                    'notify_req'   => false,
                    'notify_agent' => false,
                    'order'        => 1,
                ],
                // DDEMP finalise → rejet définitif (UC010 scénario 6.a)
                [
                    'from'         => 'Finalisation — DDEMP',
                    'to'           => 'Rejet définitif autorisation',
                    'condition'    => 'cloture',
                    'status'       => 'rejete_clos',
                    'notify_req'   => true,
                    'notify_agent' => false,
                    'order'        => 2,
                ],
                // Arrêté uploadé → Ministre valide session (UC013→UC014)
                [
                    'from'         => 'Upload arrêté — DDEMP',
                    'to'           => 'Validation session — Ministre',
                    'condition'    => 'validation',
                    'status'       => 'en_attente_arrete',
                    'notify_req'   => false,
                    'notify_agent' => true,
                    'order'        => 1,
                ],
                // Ministre valide → génération autorisation (UC014→UC015)
                [
                    'from'         => 'Validation session — Ministre',
                    'to'           => 'Génération autorisation — Système',
                    'condition'    => 'signature',
                    'status'       => 'finalise',
                    'notify_req'   => false,
                    'notify_agent' => false,
                    'order'        => 1,
                ],
                // Génération → clôture + notif usager (UC015)
                [
                    'from'         => 'Génération autorisation — Système',
                    'to'           => 'Clôture autorisation',
                    'condition'    => 'auto',
                    'status'       => 'finalise',
                    'notify_req'   => true,   // UC015 : invitation télécharger
                    'notify_agent' => false,
                    'order'        => 1,
                ],
            ];

            foreach ($transitions as $t) {
                DB::table('workflow_transitions')->insert([
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
            }

            // ----------------------------------------------------------------
            // 5. DOCUMENT PRODUIT — autorisation finale (UC015)
            //    Champs supplémentaires vs flux 4a :
            //    dénomination + adresse école, nom + prénom promoteur
            //    → stockés dans requetes.step_data (JSON), injectés dans le template
            // ----------------------------------------------------------------
            $slug = 'autorisation_ouverture_' . last(explode('-', $pData['slug']));

            DB::table('etape_document_produits')->insertOrIgnore([
                'prestation_id'       => $prestationId,
                'name'                => 'Autorisation d\'ouverture — ' . last(explode('— ', $pData['name'])),
                'slug'                => $slug,
                'type'                => 'decision',
                'numero_prefix'       => 'AOE',
                'template_key'        => 'documents.ouverture_ecole.' . last(explode('-', $pData['slug'])),
                'etape_edition_id'    => $eid('Génération autorisation — Système'),
                'etape_delivrance_id' => $eid('Clôture autorisation'),
                'allow_correction'    => false,
                'is_auto_signed'      => true,
                'is_selectable'       => false,
                'order'               => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $docId = DB::table('etape_document_produits')
                ->where('prestation_id', $prestationId)
                ->where('slug', $slug)
                ->value('id');

            DB::table('document_circuit_etapes')->insert([
                'doc_produit_id'       => $docId,
                'unite_admin_id'       => null,
                'role_name'            => 'Système',
                'action_type'          => 'edition',
                'status_after'         => 'complet',
                'requete_status_after' => 'finalise',
                'is_blocking'          => true,
                'order'                => 1,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // ----------------------------------------------------------------
            // 6. DOCUMENTS REQUIS PAR LE REQUÉRANT
            // ----------------------------------------------------------------
            $docsRequerant = [
                ['etape' => 'Dépôt demande autorisation',
                 'name'  => 'Titre foncier / Bail de location',
                 'slug'  => 'titre_foncier', 'req' => true, 'order' => 1],
                ['etape' => 'Dépôt demande autorisation',
                 'name'  => 'Plan de masse du bâtiment',
                 'slug'  => 'plan_masse', 'req' => true, 'order' => 2],
                ['etape' => 'Dépôt demande autorisation',
                 'name'  => 'Statuts de l\'établissement',
                 'slug'  => 'statuts_etablissement', 'req' => true, 'order' => 3],
                ['etape' => 'Dépôt demande autorisation',
                 'name'  => 'Pièce d\'identité du promoteur',
                 'slug'  => 'piece_identite_promoteur', 'req' => true, 'order' => 4],
                ['etape' => 'Dépôt demande autorisation',
                 'name'  => 'Diplôme(s) du directeur',
                 'slug'  => 'diplomes_directeur', 'req' => true, 'order' => 5],
                ['etape' => 'Correction demande — Usager',
                 'name'  => 'Pièces complémentaires',
                 'slug'  => 'pieces_complementaires', 'req' => false, 'order' => 1],
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
            // 7. MOTIFS DE REJET — UC004 (CS) + UC008 (CCD)
            // ----------------------------------------------------------------
            $motifs = [
                ['etape' => 'Prise en charge — Agent CS',
                 'code' => 'PIECES_MANQUANTES',    'libelle' => 'Pièces manquantes',
                 'complement' => true,  'final' => false, 'order' => 1],
                ['etape' => 'Prise en charge — Agent CS',
                 'code' => 'DOSSIER_INCOMPLET',    'libelle' => 'Dossier incomplet',
                 'complement' => true,  'final' => false, 'order' => 2],
                ['etape' => 'Étude dossier — CCD',
                 'code' => 'VISITE_DEFAVORABLE',   'libelle' => 'Visite de site défavorable',
                 'complement' => true,  'final' => false, 'order' => 1],
                ['etape' => 'Étude dossier — CCD',
                 'code' => 'NON_CONFORME_NORMES',  'libelle' => 'Non conforme aux normes',
                 'complement' => true,  'final' => false, 'order' => 2],
                ['etape' => 'Finalisation — DDEMP',
                 'code' => 'REJETE_COS_DEFINITIF', 'libelle' => 'Rejeté — décision définitive',
                 'complement' => false, 'final' => true,  'order' => 1],
            ];

            foreach ($motifs as $m) {
                DB::table('motifs_rejet')->insertOrIgnore([
                    'prestation_id'    => $prestationId,
                    'etape_id'         => $eid($m['etape']),
                    'code'             => $m['code'],
                    'libelle'          => $m['libelle'],
                    'allow_complement' => $m['complement'],
                    'is_final'         => $m['final'],
                    'is_active'        => true,
                    'order'            => $m['order'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            $this->command->info("Prestation '{$pData['name']}' configurée.");
        }

        // ----------------------------------------------------------------
        // RAPPORT FINAL
        // ----------------------------------------------------------------
        $this->command->table(
            ['Élément', 'Détail'],
            [
                ['Prestations configurées',     '2 (création + extension)'],
                ['Étapes spécifiques flux 4b',  '3 (DDEMP, visite site, CCD)'],
                ['Étapes réutilisées flux 4a',  '9 (partagées via insertOrIgnore)'],
                ['Transitions par prestation',  count($transitions ?? [])],
                ['Nouveaux statuts',            '3 (téléchargé, visite site, CCD)'],
                ['Documents requis',            count($docsRequerant ?? [])],
                ['Motifs de rejet',             count($motifs ?? [])],
                ['Nouvelles migrations',        '0 — modèle existant suffisant'],
            ]
        );

        DB::table('roles')->insertOrIgnore(['name' => 'CCD', 'guard_name' => 'api']);
        
        $this->command->newLine();
        $this->command->line('Acteur CCD : créer le rôle dans Spatie si absent.');
        $this->command->line("  DB::table('roles')->insertOrIgnore(['name' => 'CCD', 'guard_name' => 'api']);");
    }
}
