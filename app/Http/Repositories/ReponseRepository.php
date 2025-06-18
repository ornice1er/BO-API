<?php

namespace App\Http\Repositories;

use App\Models\Reponse;
use App\Traits\Repository;

class ReponseRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Reponse
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Reponse::class);
    }

    /**
     * Check if reponse exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all reponses with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Reponse::ignoreRequest(['per_page'])
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
     * Get a specific reponse by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new reponse
     */
  public function makeStore(array $data): Reponse
{


    // Création de l'utilisateur
    $reponse = Reponse::create($data);

    return $reponse;
}


    /**
     * Update an existing reponse
     */
  public function makeUpdate($id, array $data): Reponse
{
    $model = Reponse::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a reponse
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest reponses
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
     * Search for reponses by name, email, or code
     */
    public function search($term)
    {
        $query = Reponse::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
