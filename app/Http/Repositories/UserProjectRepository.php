<?php

namespace App\Http\Repositories;

use App\Models\UserProject;
use App\Traits\Repository;

class UserProjectRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var UserProject
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(UserProject::class);
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

        $req = $this->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with(['project', 'user', 'roles'])
            ->orderByDesc('created_at');

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
        return $this->findOrFail($id)->load(['project', 'user', 'roles']);
    }

    /**
     * To store model
     */
    public function makeStore($data): UserProject
    {
        $role = [];

        $role = $data['roles'];
        unset($data['roles']);

        $model = new UserProject($data);
        $model->save();

        $model->roles()->attach($role);

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): UserProject
    {

        // dd('ici');
        $model = UserProject::findOrFail($id);

        $role = $data['roles'];
        unset($data['roles']);

        $model->update($data);

        $model->roles()->detach();
        $model->roles()->attach($role);

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
