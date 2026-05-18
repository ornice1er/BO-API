<?php

namespace App\Http\Repositories;

use App\Models\EtapeVisibilite;
use App\Traits\Repository;

class EtapeVisibiliteRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(EtapeVisibilite::class);
    }

    public function getAll($request)
    {
        return EtapeVisibilite::with([
            'transition.prestation',
            'transition.etapeFrom',
            'transition.etapeTo',
            'docProduit',
        ])
            ->orderBy('workflow_transition_id')
            ->get();
    }

    public function get($id)
    {
        return EtapeVisibilite::with([
            'transition.prestation',
            'transition.etapeFrom',
            'transition.etapeTo',
            'docProduit',
        ])->findOrFail($id);
    }

    public function makeStore($data): EtapeVisibilite
    {
        $model = new EtapeVisibilite($data);
        $model->save();

        return $model->load(['transition.prestation', 'transition.etapeFrom', 'transition.etapeTo', 'docProduit']);
    }

    public function makeUpdate($id, $data): EtapeVisibilite
    {
        $model = EtapeVisibilite::findOrFail($id);
        $model->update($data);

        return $model->load(['transition.prestation', 'transition.etapeFrom', 'transition.etapeTo', 'docProduit']);
    }

    public function makeDestroy($id)
    {
        return EtapeVisibilite::findOrFail($id)->delete();
    }

    public function destroyByPrestation(int $prestationId): int
    {
        $transitionIds = \App\Models\WorkflowTransition::where('prestation_id', $prestationId)
            ->pluck('id');

        return EtapeVisibilite::whereIn('workflow_transition_id', $transitionIds)->delete();
    }

    public function copyFromPrestation(int $fromId, int $toId): int
    {
        // Supprimer les règles existantes de la destination
        $this->destroyByPrestation($toId);

        $sourceTransitions = \App\Models\WorkflowTransition::where('prestation_id', $fromId)->get();
        $targetTransitions = \App\Models\WorkflowTransition::where('prestation_id', $toId)->get();

        $count = 0;
        foreach ($sourceTransitions as $src) {
            // Trouver la transition cible structurellement équivalente
            $target = $targetTransitions->first(fn($t) =>
                $t->etape_from_id  === $src->etape_from_id  &&
                $t->etape_to_id    === $src->etape_to_id    &&
                $t->condition_type === $src->condition_type  &&
                $t->order          == $src->order
            );
            if (!$target) continue;

            $rules = EtapeVisibilite::where('workflow_transition_id', $src->id)->get();
            foreach ($rules as $rule) {
                $copy = $rule->replicate();
                $copy->workflow_transition_id = $target->id;
                $copy->save();
                $count++;
            }
        }
        return $count;
    }

    public function search($term)
    {
        return EtapeVisibilite::with([
            'transition.prestation',
            'transition.etapeFrom',
            'transition.etapeTo',
            'docProduit',
        ])
            ->where('role_name', 'like', "%{$term}%")
            ->orWhereHas('transition.prestation', fn($q) => $q->where('name', 'like', "%{$term}%"))
            ->get();
    }
}
