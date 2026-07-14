<?php

namespace App\Console\Commands;

use App\Models\Etape;
use App\Models\EtapePrestation;
use App\Models\Prestation;
use App\Models\WorkflowTransition;
use Illuminate\Console\Command;

/**
 * Audit des étapes partagées entre plusieurs e-services.
 *
 * Les étapes sont globales : leurs champs comportementaux (SLA, unité, RDV,
 * association à une session) sont désormais contextualisés par prestation
 * (table `etape_prestations`). Le backfill a recopié l'existant à l'identique,
 * donc SANS corriger les valeurs qui auraient pu être écrasées quand plusieurs
 * e-services se partageaient la même étape.
 *
 * Cette commande dresse la liste des étapes concernées : ce sont les SEULES
 * qui demandent un arbitrage métier. Les étapes utilisées par un seul e-service
 * sont exactes par construction.
 *
 * Usage : php artisan etapes:audit-partage
 */
class AuditEtapesPartagees extends Command
{
    protected $signature = 'etapes:audit-partage {--tout : Afficher aussi les étapes exclusives à un seul e-service}';

    protected $description = 'Liste les étapes partagées entre plusieurs e-services (à recalibrer)';

    public function handle(): int
    {
        // Étape → e-services qui l'utilisent dans leur graphe
        $usage = [];
        foreach (WorkflowTransition::all() as $t) {
            foreach ([$t->etape_from_id, $t->etape_to_id] as $etapeId) {
                if ($etapeId) {
                    $usage[$etapeId][$t->prestation_id] = true;
                }
            }
        }

        $prestations = Prestation::pluck('name', 'id');
        $partagees   = [];
        $exclusives  = 0;

        foreach ($usage as $etapeId => $prestationIds) {
            if (count($prestationIds) < 2) {
                $exclusives++;
                if (!$this->option('tout')) {
                    continue;
                }
            }

            $etape = Etape::find($etapeId);
            if (!$etape) {
                continue;
            }

            foreach (array_keys($prestationIds) as $prestationId) {
                $effectif = EtapePrestation::resoudre($prestationId, $etapeId);
                $override = EtapePrestation::where('prestation_id', $prestationId)
                    ->where('etape_id', $etapeId)
                    ->first();

                $partagees[] = [
                    'Étape'       => $etape->name,
                    'E-service'   => $prestations[$prestationId] ?? "#$prestationId",
                    'Partagée'    => count($prestationIds) > 1 ? count($prestationIds) . ' e-services' : '—',
                    'SLA'         => $effectif['sla_days'] ?? '—',
                    'Unité'       => $effectif['unite_admin_id'] ?? '—',
                    'RDV'         => $effectif['need_meeting'] ? 'oui' : 'non',
                    'Session'     => $effectif['can_associate'] ? 'oui' : 'non',
                    'Origine'     => $override ? 'contextualisée' : 'héritée',
                ];
            }
        }

        $nbPartagees = collect($usage)->filter(fn($p) => count($p) > 1)->count();

        $this->newLine();
        $this->info('Étapes utilisées dans un graphe : ' . count($usage));
        $this->line('  • exclusives à un seul e-service : ' . $exclusives . '  (exactes par construction, rien à faire)');
        $this->line('  • partagées par plusieurs e-services : ' . $nbPartagees . '  (à arbitrer)');
        $this->newLine();

        if (empty($partagees)) {
            $this->info('Aucune étape partagée : aucune recalibration nécessaire.');

            return self::SUCCESS;
        }

        $this->table(
            ['Étape', 'E-service', 'Partagée', 'SLA', 'Unité', 'RDV', 'Session', 'Origine'],
            $partagees
        );

        $this->newLine();
        $this->warn('Ces étapes portent aujourd\'hui la MÊME valeur pour tous les e-services qui les utilisent.');
        $this->line('Là où cette valeur devrait différer (un délai, une unité, une visite), surchargez-la depuis');
        $this->line('l\'écran : Configurations → Étapes par prestation.');

        return self::SUCCESS;
    }
}
