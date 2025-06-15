<?php

namespace App\Http\Repositories;

use App\Models\Department;
use App\Models\Municipality;
use App\Traits\Repository;

class DepartmentRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Department
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Department::class);
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

        $req = $this->ignoreRequest(['per_page', 'with', 'municipality_ids', 'only_count'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('municipality_ids', $request->all())) {
            $depatementIds = Municipality::whereIn('id', explode(',', $request['municipality_ids']))->pluck('department_id')->unique()->values()->toArray();
            $req = $req->whereIn('id', $depatementIds);
        }
        if (array_key_exists('with', $request->all())) {
            $req->with($request->with);
        }

        if (array_key_exists('only_count', $request->all())) {
            return $req->count();
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
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * To store model
     */
    public function makeStore($data): Department
    {
        $model = new Department($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): Department
    {
        $model = Department::findOrFail($id);
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
        $query = $this->query(); // Commencer avec une requête vide
        $attrs = ['code', 'name'];
        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Retourner les résultats
    }
}
