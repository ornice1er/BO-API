<?php

namespace App\Http\Repositories;

use App\Models\Permission;
use App\Traits\Repository;

class PermissionRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Permission
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Permission::class);
    }

    /**
     * Check if permission exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all permissions with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Permission::ignoreRequest(['per_page', 'with'])->filter(array_filter($request->all(), function ($k) {
            return $k != 'page';
        }, ARRAY_FILTER_USE_KEY));

        // ->orderByDesc('created_at');

        if (array_key_exists('with', $request->all()) && $request->with == 'group') {
            $req->select('label_name', 'group_name', 'created_at');
            $req->distinct();
        }

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            if (array_key_exists('with', $request->all()) && $request->with == 'group') {
                return $req->get()->groupby('group_name');

            } else {
                return $req->get();
            }

        }
    }

    /**
     * Get a specific permission by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Store a new permission
     */
    public function makeStore($data): Permission
    {
        $model = new Permission($data);
        $model->save();

        return $model;
    }

    /**
     * Update an existing permission
     */
    public function makeUpdate($id, $data): Permission
    {
        $model = Permission::findOrFail($id);
        $model->update($data);

        return $model;
    }

    /**
     * Delete a permission
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest permissions
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    /**
     * Search for permissions by name or guard_name
     */
    public function search($term)
    {
        $query = Permission::query(); // Start with an empty query
        $attrs = ['name', 'guard_name']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
