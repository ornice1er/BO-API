<?php

namespace App\Http\Repositories;

use App\Models\ContratP;
use App\Traits\Repository;

class ContratPRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var ContratP
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(ContratP::class);
    }

    /**
     * Check if contratP exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all contratPs with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = ContratP::ignoreRequest(['per_page'])
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
     * Get a specific contratP by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new contratP
     */
  public function makeStore(array $data): ContratP
{


    // Création de l'utilisateur
    $contratP = ContratP::create($data);

    return $contratP;
}


    /**
     * Update an existing contratP
     */
  public function makeUpdate($id, array $data): ContratP
{
    $model = ContratP::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a contratP
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest contratPs
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
     * Search for contratPs by name, email, or code
     */
    public function search($term)
    {
        $query = ContratP::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
