<?php

namespace App\Http\Repositories;

use App\Models\TypeEntite;
use App\Traits\Repository;

class TypeEntiteRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var TypeEntite
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(TypeEntite::class);
    }

    /**
     * Check if typeentite exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all typeentites with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = TypeEntite::ignoreRequest(['per_page'])
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
     * Get a specific typeentite by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new typeentite
     */
  public function makeStore(array $data): TypeEntite
{


    // Création de l'utilisateur
    $typeentite = TypeEntite::create($data);

    return $typeentite;
}


    /**
     * Update an existing typeentite
     */
  public function makeUpdate($id, array $data): TypeEntite
{
    $model = TypeEntite::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a typeentite
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest typeentites
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
     * Search for typeentites by name, email, or code
     */
    public function search($term)
    {
        $query = TypeEntite::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
