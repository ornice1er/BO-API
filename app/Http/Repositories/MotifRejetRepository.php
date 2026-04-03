<?php

namespace App\Http\Repositories;

use App\Models\MotifRejet;
use App\Traits\Repository;

class MotifRejetRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(MotifRejet::class);
    }

    public function getAll($request)
    {
        return MotifRejet::with(['prestation', 'etape'])
            ->orderBy('prestation_id')
            ->orderBy('order')
            ->get();
    }

    public function get($id)
    {
        return MotifRejet::with(['prestation', 'etape'])
            ->findOrFail($id);
    }

    public function makeStore($data): MotifRejet
    {
        $model = new MotifRejet($data);
        $model->save();

        return $model->load(['prestation', 'etape']);
    }

    public function makeUpdate($id, $data): MotifRejet
    {
        $model = MotifRejet::findOrFail($id);
        $model->update($data);

        return $model->load(['prestation', 'etape']);
    }

    public function makeDestroy($id)
    {
        return MotifRejet::findOrFail($id)->delete();
    }

    public function search($term)
    {
        return MotifRejet::with(['prestation', 'etape'])
            ->where('libelle', 'like', "%{$term}%")
            ->orWhere('code', 'like', "%{$term}%")
            ->orderBy('order')
            ->get();
    }
}
