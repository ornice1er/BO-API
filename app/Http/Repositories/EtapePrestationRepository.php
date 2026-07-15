<?php

namespace App\Http\Repositories;

use App\Models\EtapePrestation;
use App\Models\WorkflowTransition;
use App\Traits\Repository;

class EtapePrestationRepository
{
    use Repository;

    protected $model;

    protected array $relations = ['prestation', 'etape', 'uniteAdmin'];

    public function __construct()
    {
        $this->model = app(EtapePrestation::class);
    }

    public function getAll($request)
    {
        $query = EtapePrestation::with($this->relations);

        if ($request->filled('prestation_id')) {
            $query->where('prestation_id', $request->input('prestation_id'));
        }

        return $query->orderBy('prestation_id')->orderBy('etape_id')->get();
    }

    public function get($id)
    {
        return EtapePrestation::with($this->relations)->findOrFail($id);
    }

    public function makeStore($data): EtapePrestation
    {
        $model = new EtapePrestation($data);
        $model->save();

        return $model->load($this->relations);
    }

    public function makeUpdate($id, $data): EtapePrestation
    {
        $model = EtapePrestation::findOrFail($id);
        $model->update($data);

        return $model->load($this->relations);
    }

    public function makeDestroy($id)
    {
        return EtapePrestation::findOrFail($id)->delete();
    }

    public function search($term)
    {
        return EtapePrestation::with($this->relations)
            ->whereHas('etape', fn($q) => $q->where('name', 'like', "%{$term}%"))
            ->orWhereHas('prestation', fn($q) => $q->where('name', 'like', "%{$term}%"))
            ->get();
    }

    /**
     * Copie la contextualisation des étapes d'une prestation source vers une ou
     * plusieurs prestations cibles. Les couples (prestation, étape) déjà présents
     * sur une cible ne sont pas dupliqués (contrainte d'unicité respectée).
     *
     * @param  int[]  $targetIds
     * @return int    Nombre de lignes créées
     */
    public function copyFromPrestation(int $fromId, array $targetIds): int
    {
        $sources = EtapePrestation::where('prestation_id', $fromId)->get();
        if ($sources->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($targetIds as $targetId) {
            $targetId = (int) $targetId;
            if ($targetId === $fromId) {
                continue;
            }

            foreach ($sources as $src) {
                $existe = EtapePrestation::where('prestation_id', $targetId)
                    ->where('etape_id', $src->etape_id)
                    ->exists();
                if ($existe) {
                    continue;
                }

                EtapePrestation::create([
                    'prestation_id'  => $targetId,
                    'etape_id'       => $src->etape_id,
                    'sla_days'       => $src->sla_days,
                    'unite_admin_id' => $src->unite_admin_id,
                    'can_associate'  => $src->can_associate,
                    'need_meeting'   => $src->need_meeting,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Étapes réellement présentes dans le graphe d'une prestation, avec leur
     * contextualisation si elle existe. Alimente l'écran de configuration :
     * l'administrateur ne se voit proposer que des étapes qui ont un sens ici.
     */
    public function etapesDuGraphe(int $prestationId)
    {
        $duGraphe = WorkflowTransition::where('prestation_id', $prestationId)
            ->get(['etape_from_id', 'etape_to_id'])
            ->flatMap(fn($t) => [$t->etape_from_id, $t->etape_to_id])
            ->filter()
            ->unique();

        // Une demande peut stationner sur une étape absente du graphe (parcours modifié
        // après son dépôt). Elle doit rester configurable, sinon on ne peut plus la piloter.
        $horsGraphe = \App\Models\Requete::where('prestation_id', $prestationId)
            ->whereNotNull('current_etape_id')
            ->pluck('current_etape_id')
            ->merge(
                EtapePrestation::where('prestation_id', $prestationId)->pluck('etape_id')
            )
            ->filter()
            ->unique()
            ->diff($duGraphe);

        $etapeIds = $duGraphe->merge($horsGraphe)->values();

        $overrides = EtapePrestation::with($this->relations)
            ->where('prestation_id', $prestationId)
            ->get()
            ->keyBy('etape_id');

        // Nombre d'e-services qui utilisent chaque étape : une étape partagée est
        // celle où une valeur mal calibrée déborde sur les autres e-services.
        $partage = WorkflowTransition::whereIn('etape_from_id', $etapeIds)
            ->orWhereIn('etape_to_id', $etapeIds)
            ->get(['prestation_id', 'etape_from_id', 'etape_to_id'])
            ->flatMap(fn($t) => array_filter([
                $t->etape_from_id ? [$t->etape_from_id, $t->prestation_id] : null,
                $t->etape_to_id   ? [$t->etape_to_id,   $t->prestation_id] : null,
            ]))
            ->groupBy(fn($paire) => $paire[0])
            ->map(fn($paires) => $paires->pluck(1)->unique()->count());

        return \App\Models\Etape::whereIn('id', $etapeIds)
            ->orderBy('name')
            ->get()
            ->map(function ($etape) use ($prestationId, $overrides, $partage, $horsGraphe) {
                $etape->contextualisation = $overrides->get($etape->id);
                $etape->effectif          = EtapePrestation::resoudre($prestationId, $etape->id);
                $etape->nb_eservices      = $partage->get($etape->id, 1);
                $etape->hors_graphe       = $horsGraphe->contains($etape->id);

                return $etape;
            });
    }
}
