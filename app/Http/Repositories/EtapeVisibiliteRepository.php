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
