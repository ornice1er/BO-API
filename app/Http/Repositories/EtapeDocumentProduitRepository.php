<?php

namespace App\Http\Repositories;

use App\Models\EtapeDocumentProduit;
use App\Traits\Repository;

class EtapeDocumentProduitRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(EtapeDocumentProduit::class);
    }

    public function getAll($request)
    {
        return EtapeDocumentProduit::with(['prestation', 'etapeEdition', 'etapeDelivrance'])
            ->orderBy('order')
            ->get();
    }

    public function get($id)
    {
        return EtapeDocumentProduit::with(['prestation', 'etapeEdition', 'etapeDelivrance'])
            ->findOrFail($id);
    }

    public function makeStore($data): EtapeDocumentProduit
    {
        $model = new EtapeDocumentProduit($data);
        $model->save();

        return $model->load(['prestation', 'etapeEdition', 'etapeDelivrance']);
    }

    public function makeUpdate($id, $data): EtapeDocumentProduit
    {
        $model = EtapeDocumentProduit::findOrFail($id);
        $model->update($data);

        return $model->load(['prestation', 'etapeEdition', 'etapeDelivrance']);
    }

    public function makeDestroy($id)
    {
        return EtapeDocumentProduit::findOrFail($id)->delete();
    }

    public function search($term)
    {
        return EtapeDocumentProduit::with(['prestation', 'etapeEdition', 'etapeDelivrance'])
            ->where('name', 'like', "%{$term}%")
            ->orWhere('template_key', 'like', "%{$term}%")
            ->orWhere('slug', 'like', "%{$term}%")
            ->orderBy('order')
            ->get();
    }
}
