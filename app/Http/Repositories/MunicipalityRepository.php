<?php

namespace App\Http\Repositories;

use App\Models\Municipality;
use App\Traits\Repository;

class MunicipalityRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Municipality
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Municipality::class);
    }

    /**
     * Check if exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all elements
     */
    public function getAll($request)
    {

        $per_page = 10;

        $req = Municipality::ignoreRequest(['per_page', 'with', 'municipality_ids'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->where('department_id', '!=', null)
            ->orderByDesc('created_at');

        if (array_key_exists('municipality_ids', $request->all()) && $request['municipality_ids'] != '') {
            $req = $req->whereIn('id', is_array($request['municipality_ids']) ? $request['municipality_ids'] : explode(',', $request['municipality_ids']));
        }
        if (array_key_exists('with', $request->all())) {
            $req->with($request->with);
        }

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);

        } else {
            return $req->get();
        }
    }

    /**
     * Get all elements
     */
    public function getAllGhm($request)
    {

        $per_page = 10;

        $req = Municipality::ignoreRequest(['per_page', 'with'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->where('project_id', '!=', null)
            ->orderByDesc('created_at');

        if (array_key_exists('with', $request->all())) {
            $req->with($request->with);
        }
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);

        } else {
            return $req->get();
        }
    }

    /**
     * Get an element
     */
    public function get($id, $with)
    {
        if (is_null($with)) {
            return $this->findOrFail($id);
        } else {
            return $this->findOrFail($id)->load($with);

        }
    }

    /**
     * To store model
     */
    public function makeStore($data): Municipality
    {
        $model = new Municipality($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): Municipality
    {
        $model = Municipality::findOrFail($id);
        $model->update($data);

        return $model;
    }

    /**
     * To delete model
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * To get all latest
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }

    /**
     * Get an element
     */
    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    public function search($term)
    {
        $query = Municipality::query(); // Commencer avec une requête vide
        $attrs = ['code', 'name'];
        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Retourner les résultats
    }
}
