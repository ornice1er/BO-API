<?php

namespace App\Http\Repositories;

use App\Models\EtapeDocument;
use App\Traits\Repository;

class EtapeDocumentRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(EtapeDocument::class);
    }

    public function getAll($request)
    {
        return EtapeDocument::with(['prestation', 'etape'])
            ->orderBy('prestation_id')
            ->orderBy('order')
            ->get();
    }

    public function get($id)
    {
        return EtapeDocument::with(['prestation', 'etape'])
            ->findOrFail($id);
    }

    public function makeStore($data): EtapeDocument
    {
        $model = new EtapeDocument($data);
        $model->save();

        return $model->load(['prestation', 'etape']);
    }

    public function makeUpdate($id, $data): EtapeDocument
    {
        $model = EtapeDocument::findOrFail($id);
        $model->update($data);

        return $model->load(['prestation', 'etape']);
    }

    public function makeDestroy($id)
    {
        return EtapeDocument::findOrFail($id)->delete();
    }

    public function search($term)
    {
        return EtapeDocument::with(['prestation', 'etape'])
            ->where('name', 'like', "%{$term}%")
            ->orWhere('slug', 'like', "%{$term}%")
            ->orderBy('order')
            ->get();
    }
}
