<?php

namespace App\Http\Repositories;

use App\Models\TypeUniteAdmin;
use App\Traits\Repository;

class TypeUniteAdminRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var TypeUniteAdmin
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(TypeUniteAdmin::class);
    }

    /**
     * Check if typeuniteadmin exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all typeuniteadmins with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = TypeUniteAdmin::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
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
     * Get a specific typeuniteadmin by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new typeuniteadmin
     */
  public function makeStore(array $data): TypeUniteAdmin
{


    // Création de l'utilisateur
    $typeuniteadmin = TypeUniteAdmin::create($data);

    return $typeuniteadmin;
}


    /**
     * Update an existing typeuniteadmin
     */
  public function makeUpdate($id, array $data): TypeUniteAdmin
{
    $model = TypeUniteAdmin::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a typeuniteadmin
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest typeuniteadmins
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    /**
     * Search for typeuniteadmins by name, email, or code
     */
    public function search($term)
    {
        $query = TypeUniteAdmin::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
