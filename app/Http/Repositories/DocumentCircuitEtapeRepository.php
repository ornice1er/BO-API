<?php

namespace App\Http\Repositories;

use App\Models\DocumentCircuitEtape;
use App\Traits\Repository;

class DocumentCircuitEtapeRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(DocumentCircuitEtape::class);
    }

    public function getAll($request)
    {
        return DocumentCircuitEtape::with(['docProduit', 'uniteAdmin'])
            ->orderBy('doc_produit_id')
            ->orderBy('order')
            ->get();
    }

    public function get($id)
    {
        return DocumentCircuitEtape::with(['docProduit', 'uniteAdmin'])
            ->findOrFail($id);
    }

    public function makeStore($data): DocumentCircuitEtape
    {
        $model = new DocumentCircuitEtape($data);
        $model->save();

        return $model->load(['docProduit', 'uniteAdmin']);
    }

    public function makeUpdate($id, $data): DocumentCircuitEtape
    {
        $model = DocumentCircuitEtape::findOrFail($id);
        $model->update($data);

        return $model->load(['docProduit', 'uniteAdmin']);
    }

    public function makeDestroy($id)
    {
        return DocumentCircuitEtape::findOrFail($id)->delete();
    }

    public function search($term)
    {
        return DocumentCircuitEtape::with(['docProduit', 'uniteAdmin'])
            ->where('role_name', 'like', "%{$term}%")
            ->orWhereHas('docProduit', fn($q) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('order')
            ->get();
    }
}
