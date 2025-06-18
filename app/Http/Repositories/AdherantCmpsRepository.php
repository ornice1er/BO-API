<?php

namespace App\Http\Repositories;

use App\Models\AdherantCmps;
use App\Traits\Repository;

class AdherantCmpsRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AdherantCmps
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AdherantCmps::class);
    }

    /**
     * Check if adherantCmps exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all adherantCmpss with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AdherantCmps::ignoreRequest(['per_page'])
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
     * Get a specific adherantCmps by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new adherantCmps
     */
  public function makeStore(array $data): AdherantCmps
{


    // Création de l'utilisateur
    $adherantCmps = AdherantCmps::create($data);

    return $adherantCmps;
}


    /**
     * Update an existing adherantCmps
     */
  public function makeUpdate($id, array $data): AdherantCmps
{
    $model = AdherantCmps::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a adherantCmps
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest adherantCmpss
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
     * Search for adherantCmpss by name, email, or code
     */
    public function search($term)
    {
        $query = AdherantCmps::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}

