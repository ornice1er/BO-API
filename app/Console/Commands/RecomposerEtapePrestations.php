<?php

namespace App\Console\Commands;

use App\Models\Etape;
use App\Models\EtapePrestation;
use App\Models\Prestation;
use App\Models\Requete;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recompose `etape_prestations` à partir de TOUT l'existant.
 *
 * Le backfill de la migration 2026_06_30_004 ne lisait que `workflow_transitions`.
 * Or un couple (prestation, étape) peut aussi exister :
 *   - dans les documents produits      (etape_edition_id / etape_delivrance_id)
 *   - dans les pièces justificatives   (etape_documents)
 *   - dans les motifs de rejet
 *   - dans les workflows (legacy)
 *   - ET SURTOUT dans les DEMANDES EN COURS (requetes.current_etape_id) : une demande
 *     peut être posée sur une étape qui ne figure plus dans le graphe de transitions.
 *
 * Sans ligne de contextualisation, la résolution retombe sur les valeurs de l'étape,
 * donc le comportement reste correct — mais l'étape resterait invisible dans l'écran
 * de configuration, et l'administrateur ne pourrait pas la calibrer. Cette commande
 * complète les couples manquants EN RECOPIANT LES VALEURS ACTUELLES : aucun flux en
 * cours n'est modifié.
 *
 * Aucun `etape_id` n'est réaffecté, aucune étape n'est dupliquée : les demandes en
 * cours, leurs logs et leurs documents continuent de pointer sur les mêmes lignes.
 *
 * Usage :
 *   php artisan etapes:recomposer            # simulation (par défaut)
 *   php artisan etapes:recomposer --appliquer
 */
class RecomposerEtapePrestations extends Command
{
    protected $signature = 'etapes:recomposer {--appliquer : Écrire réellement les lignes manquantes}';

    protected $description = 'Complète les étapes par prestation à partir de tout l\'existant (transitions, documents, demandes en cours)';

    public function handle(): int
    {
        $appliquer = (bool) $this->option('appliquer');

        $couples = $this->recenserCouples();
        $this->info('Couples (prestation, étape) référencés dans l\'existant : ' . count($couples));

        $manquants = [];
        foreach ($couples as $cle => $origines) {
            [$prestationId, $etapeId] = array_map('intval', explode('-', $cle));

            $existe = EtapePrestation::where('prestation_id', $prestationId)
                ->where('etape_id', $etapeId)
                ->exists();

            if (!$existe) {
                $manquants[$cle] = $origines;
            }
        }

        $this->line('  • déjà contextualisés : ' . (count($couples) - count($manquants)));
        $this->line('  • manquants           : ' . count($manquants));
        $this->newLine();

        if (empty($manquants)) {
            $this->info('Rien à recomposer : tout l\'existant est couvert.');

            return $this->verifierDemandesEnCours();
        }

        $prestations = Prestation::pluck('name', 'id');
        $lignes      = [];

        foreach ($manquants as $cle => $origines) {
            [$prestationId, $etapeId] = array_map('intval', explode('-', $cle));
            $etape = Etape::find($etapeId);

            $lignes[] = [
                'E-service' => $prestations[$prestationId] ?? "#$prestationId",
                'Étape'     => $etape?->name ?? "#$etapeId (INTROUVABLE)",
                'Vu dans'   => implode(', ', array_keys($origines)),
                'SLA'       => $etape?->sla_days ?? '—',
                'Unité'     => $etape?->unite_admin_id ?? '—',
                'RDV'       => $etape?->need_meeting ? 'oui' : 'non',
                'Session'   => $etape?->can_associate ? 'oui' : 'non',
            ];
        }

        $this->table(['E-service', 'Étape', 'Vu dans', 'SLA', 'Unité', 'RDV', 'Session'], $lignes);
        $this->newLine();

        if (!$appliquer) {
            $this->warn('SIMULATION — aucune écriture. Relancez avec --appliquer pour créer ces lignes.');
            $this->line('Les valeurs recopiées sont celles portées aujourd\'hui par l\'étape : le comportement');
            $this->line('des demandes en cours reste strictement identique.');

            return self::SUCCESS;
        }

        $crees = 0;
        DB::transaction(function () use ($manquants, &$crees) {
            foreach ($manquants as $cle => $origines) {
                [$prestationId, $etapeId] = array_map('intval', explode('-', $cle));
                $etape = Etape::find($etapeId);
                if (!$etape) {
                    $this->warn("Étape #$etapeId introuvable — couple ignoré.");
                    continue;
                }

                EtapePrestation::create([
                    'prestation_id'  => $prestationId,
                    'etape_id'       => $etapeId,
                    'sla_days'       => $etape->sla_days,
                    'unite_admin_id' => $etape->unite_admin_id,
                    'can_associate'  => $etape->can_associate,
                    'need_meeting'   => $etape->need_meeting,
                ]);
                $crees++;
            }
        });

        $this->info("$crees ligne(s) créée(s) — valeurs recopiées à l'identique.");
        $this->newLine();

        return $this->verifierDemandesEnCours();
    }

    /**
     * Tous les couples (prestation, étape) présents dans l'existant, avec leur origine.
     *
     * @return array<string, array<string, true>>  clé "prestationId-etapeId"
     */
    private function recenserCouples(): array
    {
        $couples = [];

        $ajouter = function (?int $prestationId, ?int $etapeId, string $origine) use (&$couples) {
            if ($prestationId && $etapeId) {
                $couples["$prestationId-$etapeId"][$origine] = true;
            }
        };

        foreach (DB::table('workflow_transitions')->get() as $r) {
            $ajouter($r->prestation_id, $r->etape_from_id, 'transitions');
            $ajouter($r->prestation_id, $r->etape_to_id, 'transitions');
        }

        foreach (DB::table('etape_document_produits')->get() as $r) {
            $ajouter($r->prestation_id, $r->etape_edition_id, 'documents produits');
            $ajouter($r->prestation_id, $r->etape_delivrance_id, 'documents produits');
        }

        foreach (DB::table('etape_documents')->get() as $r) {
            $ajouter($r->prestation_id, $r->etape_id, 'pièces justificatives');
        }

        foreach (DB::table('motifs_rejet')->get() as $r) {
            $ajouter($r->prestation_id, $r->etape_id, 'motifs de rejet');
        }

        foreach (DB::table('workflows')->get() as $r) {
            $ajouter($r->prestation_id, $r->etape_id ?? null, 'workflows');
        }

        // Le plus important : les demandes déjà en circulation.
        foreach (DB::table('requetes')->get() as $r) {
            $ajouter($r->prestation_id, $r->current_etape_id, 'DEMANDES EN COURS');
        }

        // Et leur historique, pour que les étapes déjà franchies restent configurables.
        foreach (DB::table('requete_etape_logs')->get() as $log) {
            $requete = DB::table('requetes')->where('id', $log->requete_id)->first();
            if (!$requete) {
                continue;
            }
            $ajouter($requete->prestation_id, $log->etape_from_id, 'historique');
            $ajouter($requete->prestation_id, $log->etape_to_id, 'historique');
        }

        return $couples;
    }

    /**
     * Garde-fou : pour chaque demande en cours, la valeur résolue doit être IDENTIQUE
     * à celle que l'ancien code lisait (les champs portés par l'étape).
     */
    private function verifierDemandesEnCours(): int
    {
        $requetes = Requete::whereNotNull('current_etape_id')->get();

        if ($requetes->isEmpty()) {
            $this->line('Aucune demande en cours à vérifier.');

            return self::SUCCESS;
        }

        $divergences = [];

        foreach ($requetes as $requete) {
            $etape = Etape::find($requete->current_etape_id);
            if (!$etape) {
                continue;
            }

            $effectif = EtapePrestation::resoudre($requete->prestation_id, $requete->current_etape_id);

            $avant = [
                'sla_days'       => $etape->sla_days,
                'unite_admin_id' => $etape->unite_admin_id,
                'can_associate'  => (bool) $etape->can_associate,
                'need_meeting'   => (bool) $etape->need_meeting,
            ];

            foreach ($avant as $champ => $valeur) {
                if ($effectif[$champ] != $valeur) {
                    $divergences[] = [
                        'Demande' => $requete->code,
                        'Étape'   => $etape->name,
                        'Champ'   => $champ,
                        'Avant'   => var_export($valeur, true),
                        'Après'   => var_export($effectif[$champ], true),
                    ];
                }
            }
        }

        if (empty($divergences)) {
            $this->info('✔ ' . $requetes->count() . ' demande(s) en cours vérifiée(s) : comportement strictement identique.');

            return self::SUCCESS;
        }

        $this->error('⚠ Des demandes en cours changeraient de comportement :');
        $this->table(['Demande', 'Étape', 'Champ', 'Avant', 'Après'], $divergences);
        $this->line('Ces écarts viennent de surcharges saisies manuellement. Vérifiez-les avant de continuer.');

        return self::FAILURE;
    }
}
