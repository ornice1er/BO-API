<?php

namespace App\Http\Repositories;

use App\Models\ProjectCategory;
use App\Traits\Repository;

class ProjectCategoryRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var ProjectCategory
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(ProjectCategory::class);
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

        $req = $this->ignoreRequest(['per_page', 'with'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
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
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * To store model
     */
    public function makeStore($data): ProjectCategory
    {
        $model = new ProjectCategory($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): ProjectCategory
    {
        $model = ProjectCategory::findOrFail($id);
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
