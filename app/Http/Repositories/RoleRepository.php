<?php

namespace App\Http\Repositories;

use App\Models\Permission;
use App\Traits\Repository;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Role
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Role::class);
    }

    /**
     * Check if role exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all roles with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Role::filter(array_filter($request->all(), function ($k) {
            return $k != 'page';
        }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Get a specific role by id
     */
    public function get($id)
    {
        return $this->findOrFail($id)->load('permissions');
    }

    /**
     * Store a new role
     */
    public function makeStore($data)
    {

        $permissions = [];
        if (array_key_exists('permissions', $data)) {
            foreach ($data['permissions'] as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $qPermission = Permission::where('group_name', $key)->where('label_name', $value2['label_name']);
                    $permission = $value2['show_edit'] ? $qPermission->where('show_edit', true)->first() : $qPermission->where('show_only', true)->first();
                    $permissions[] = $permission;
                }

            }
            unset($data['permissions']);
            $model = new Role($data);
            $model->save();
            $model->syncPermissions($permissions);

            return $model->load('permissions');

        } else {
            $model = new Role($data);
            $model->save();

            return $model;

        }

    }

    /**
     * Update an existing role
     */
    public function makeUpdate($id, $data): Role
    {

        $permissions = [];
        if (array_key_exists('permissions', $data)) {
            foreach ($data['permissions'] as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $qPermission = Permission::where('group_name', $key)->where('label_name', $value2['label_name']);
                    $permission = $value2['show_edit'] ? $qPermission->where('show_edit', true)->first() : $qPermission->where('show_only', true)->first();
                    $permissions[] = $permission;
                }

            }

            $model = Role::findOrFail($id);
            $model->syncPermissions($permissions);

            return $model->load('permissions');
        } else {
            $model = Role::findOrFail($id);
            $model->update($data);

            return $model;

        }
    }

    /**
     * Delete a role
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest roles
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    /**
     * Search for roles by name or code
     */
    public function search($term)
    {
        $query = Role::query(); // Start with an empty query
        $attrs = ['name', 'guard_name']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
