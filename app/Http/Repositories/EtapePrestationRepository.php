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
     * Étapes réellement présentes dans le graphe d'une prestation, avec leur
     * contextualisation si elle existe. Alimente l'écran de configuration :
     * l'administrateur ne se voit proposer que des étapes qui ont un sens ici.
     */
    public function etapesDuGraphe(int $prestationId)
    {
        $etapeIds = WorkflowTransition::where('prestation_id', $prestationId)
            ->get(['etape_from_id', 'etape_to_id'])
            ->flatMap(fn($t) => [$t->etape_from_id, $t->etape_to_id])
            ->filter()
            ->unique()
            ->values();

        $overrides = EtapePrestation::with($this->relations)
            ->where('prestation_id', $prestationId)
            ->get()
            ->keyBy('etape_id');

        return \App\Models\Etape::whereIn('id', $etapeIds)
            ->orderBy('name')
            ->get()
            ->map(function ($etape) use ($prestationId, $overrides) {
                $etape->contextualisation = $overrides->get($etape->id);
                $etape->effectif          = EtapePrestation::resoudre($prestationId, $etape->id);

                return $etape;
            });
    }
}
