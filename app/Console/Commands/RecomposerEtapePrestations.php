<?php

namespace App\Console\Commands;

use App\Models\Etape;
use App\Models\EtapePrestation;
use App\Models\Prestation;
use App\Models\Requete;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recompose `etape_prestations` à partir du PARCOURS RÉEL de chaque prestation.
 *
 * Deux objectifs, pour remettre d'aplomb les données déjà en place :
 *
 *  1. LIER LES BONNES ÉTAPES — ne retenir que les couples (prestation, étape)
 *     qui appartiennent réellement au parcours de la prestation :
 *       - workflow_transitions (etape_from / etape_to)
 *       - workflows (modèle legacy)
 *       - etape_document_produits (édition / délivrance)
 *       - etape_documents (pièces justificatives)
 *       - motifs_rejet
 *       - requetes.current_etape_id (demandes en cours — réellement posées là)
 *     L'HISTORIQUE (requete_etape_logs) est volontairement EXCLU : une étape
 *     seulement franchie par de vieilles demandes n'est pas forcément une étape
 *     du parcours actuel, et créait des couples parasites.
 *
 *  2. RESYNCHRONISER LES CHAMPS COMPORTEMENTAUX — pour chaque couple retenu
 *     (déjà présent OU manquant), (ré)écrire les 4 champs depuis l'étape globale :
 *     sla_days, unite_admin_id, can_associate, need_meeting.
 *
 * ⚠️ La resynchronisation ÉCRASE toute valeur par prestation avec la valeur
 * globale de l'étape. C'est l'effet recherché AU MOMENT DE LA RECOMPOSITION
 * (avant toute calibration manuelle). À ne pas relancer après avoir surchargé
 * des étapes à la main, sous peine de perdre ces surcharges.
 *
 * Aucun `etape_id` n'est réaffecté, aucune étape n'est dupliquée : les demandes
 * en cours, leurs logs et leurs documents continuent de pointer sur les mêmes
 * lignes. Les couples « orphelins » (présents en base mais hors parcours) sont
 * SIGNALÉS, jamais supprimés automatiquement.
 *
 * Usage :
 *   php artisan etapes:recomposer              # simulation (par défaut)
 *   php artisan etapes:recomposer --appliquer
 */
class RecomposerEtapePrestations extends Command
{
    protected $signature = 'etapes:recomposer {--appliquer : Écrire réellement les créations et resynchronisations}';

    protected $description = 'Lie les bonnes étapes aux prestations et resynchronise les champs comportementaux (données existantes)';

    public function handle(): int
    {
        $appliquer = (bool) $this->option('appliquer');

        $couples = $this->recenserCouplesLegitimes();
        $this->info('Couples (prestation, étape) légitimes recensés : ' . count($couples));

        $prestations = Prestation::pluck('name', 'id');

        $aCreer     = [];   // couples sans ligne
        $aResync    = [];   // couples avec ligne mais valeurs différentes de l'étape
        $inchanges  = 0;    // couples déjà alignés

        foreach ($couples as $cle => $origines) {
            [$prestationId, $etapeId] = array_map('intval', explode('-', $cle));
            $etape = Etape::find($etapeId);
            if (!$etape) {
                continue;
            }

            $cible = [
                'sla_days'       => $etape->sla_days,
                'unite_admin_id' => $etape->unite_admin_id,
                'can_associate'  => (int) (bool) $etape->can_associate,
                'need_meeting'   => (int) (bool) $etape->need_meeting,
            ];

            $ligne = EtapePrestation::where('prestation_id', $prestationId)
                ->where('etape_id', $etapeId)
                ->first();

            $descr = [
                'E-service' => $prestations[$prestationId] ?? "#$prestationId",
                'Étape'     => $etape->name,
                'Vu dans'   => implode(', ', array_keys($origines)),
                'SLA'       => $cible['sla_days'] ?? '—',
                'Unité'     => $cible['unite_admin_id'] ?? '—',
                'RDV'       => $cible['need_meeting'] ? 'oui' : 'non',
                'Session'   => $cible['can_associate'] ? 'oui' : 'non',
            ];

            if (!$ligne) {
                $aCreer[$cle] = ['descr' => $descr, 'p' => $prestationId, 'e' => $etapeId, 'v' => $cible];
                continue;
            }

            $actuel = [
                'sla_days'       => $ligne->sla_days,
                'unite_admin_id' => $ligne->unite_admin_id,
                'can_associate'  => (int) (bool) $ligne->can_associate,
                'need_meeting'   => (int) (bool) $ligne->need_meeting,
            ];

            if ($actuel == $cible) {
                $inchanges++;
            } else {
                $aResync[$cle] = ['descr' => $descr, 'id' => $ligne->id, 'v' => $cible];
            }
        }

        $orphelins = $this->recenserOrphelins($couples, $prestations);

        $this->line('  • à créer          : ' . count($aCreer));
        $this->line('  • à resynchroniser : ' . count($aResync));
        $this->line('  • déjà alignés     : ' . $inchanges);
        $this->line('  • orphelins (hors parcours, signalés) : ' . count($orphelins));
        $this->newLine();

        if (!empty($aCreer)) {
            $this->comment('Lignes à CRÉER :');
            $this->table(
                ['E-service', 'Étape', 'Vu dans', 'SLA', 'Unité', 'RDV', 'Session'],
                array_map(fn($x) => $x['descr'], $aCreer)
            );
        }

        if (!empty($aResync)) {
            $this->comment('Lignes à RESYNCHRONISER (valeurs réécrites depuis l\'étape) :');
            $this->table(
                ['E-service', 'Étape', 'Vu dans', 'SLA', 'Unité', 'RDV', 'Session'],
                array_map(fn($x) => $x['descr'], $aResync)
            );
        }

        if (!empty($orphelins)) {
            $this->warn('Lignes ORPHELINES — présentes en base mais hors parcours actuel. NON supprimées :');
            $this->table(['E-service', 'Étape', 'Ligne #'], $orphelins);
            $this->line('Vérifiez-les : soit ce sont des surcharges volontaires à conserver, soit des');
            $this->line('résidus à supprimer manuellement depuis l\'écran « Étapes par prestation ».');
            $this->newLine();
        }

        if (empty($aCreer) && empty($aResync)) {
            $this->info('Rien à recomposer : tout le parcours est déjà lié et synchronisé.');

            return $this->verifierDemandesEnCours();
        }

        if (!$appliquer) {
            $this->warn('SIMULATION — aucune écriture. Relancez avec --appliquer pour créer et resynchroniser.');
            $this->line('Les valeurs écrites sont celles portées par l\'étape globale : le comportement');
            $this->line('des demandes en cours reste identique.');

            return self::SUCCESS;
        }

        $crees = 0;
        $syncs = 0;
        DB::transaction(function () use ($aCreer, $aResync, &$crees, &$syncs) {
            foreach ($aCreer as $x) {
                EtapePrestation::create(array_merge(
                    ['prestation_id' => $x['p'], 'etape_id' => $x['e']],
                    $x['v']
                ));
                $crees++;
            }
            foreach ($aResync as $x) {
                EtapePrestation::where('id', $x['id'])->update($x['v']);
                $syncs++;
            }
        });

        $this->info("$crees ligne(s) créée(s), $syncs resynchronisée(s) — valeurs alignées sur l'étape globale.");
        $this->newLine();

        return $this->verifierDemandesEnCours();
    }

    /**
     * Couples (prestation, étape) appartenant au parcours réel des prestations.
     * L'historique des demandes est volontairement exclu.
     *
     * @return array<string, array<string, true>>  clé "prestationId-etapeId"
     */
    private function recenserCouplesLegitimes(): array
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

        foreach (DB::table('workflows')->get() as $r) {
            $ajouter($r->prestation_id, $r->etape_id ?? null, 'workflows');
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

        // Demandes en cours : une demande réellement posée sur une étape en fait
        // partie du parcours. (Contrairement à l'historique, exclu.)
        foreach (DB::table('requetes')->whereNotNull('current_etape_id')->get() as $r) {
            $ajouter($r->prestation_id, $r->current_etape_id, 'demandes en cours');
        }

        return $couples;
    }

    /**
     * Lignes existantes dont le couple (prestation, étape) n'est pas dans le
     * parcours légitime. Signalées, jamais supprimées.
     *
     * @param  array<string, mixed>  $couplesLegitimes
     * @return array<int, array{E-service: string, Étape: string, 'Ligne #': int}>
     */
    private function recenserOrphelins(array $couplesLegitimes, $prestations): array
    {
        $orphelins = [];

        EtapePrestation::with('etape')->get()->each(function ($ligne) use ($couplesLegitimes, $prestations, &$orphelins) {
            $cle = $ligne->prestation_id . '-' . $ligne->etape_id;
            if (!isset($couplesLegitimes[$cle])) {
                $orphelins[] = [
                    'E-service' => $prestations[$ligne->prestation_id] ?? "#{$ligne->prestation_id}",
                    'Étape'     => $ligne->etape?->name ?? "#{$ligne->etape_id}",
                    'Ligne #'   => $ligne->id,
                ];
            }
        });

        return $orphelins;
    }

    /**
     * Garde-fou : pour chaque demande en cours, la valeur résolue doit rester
     * IDENTIQUE à celle que l'ancien code lisait (les champs portés par l'étape).
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

        return self::FAILURE;
    }
}
