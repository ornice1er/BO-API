<?php

namespace App\Http\Repositories;

use App\Models\FonctionAgent;
use App\Traits\Repository;

class FonctionAgentRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var FonctionAgent
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(FonctionAgent::class);
    }

    /**
     * Check if fonctionagentt exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all fonctionagentts with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = FonctionAgent::ignoreRequest(['per_page'])
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
     * Get a specific fonctionagentt by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new fonctionagentt
     */
  public function makeStore(array $data): FonctionAgent
{


    // Création de l'utilisateur
    $fonctionagentt = FonctionAgent::create($data);

    return $fonctionagentt;
}


    /**
     * Update an existing fonctionagentt
     */
  public function makeUpdate($id, array $data): FonctionAgent
{
    $model = FonctionAgent::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a fonctionagentt
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest fonctionagentts
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
     * Search for fonctionagentts by name, email, or code
     */
    public function search($term)
    {
        $query = FonctionAgent::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
