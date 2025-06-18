<?php

namespace App\Http\Repositories;

use App\Models\Prestation;
use App\Traits\Repository;

class PrestationRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Prestation
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Prestation::class);
    }

    /**
     * Check if prestation exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all prestations with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Prestation::ignoreRequest(['per_page'])
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
     * Get a specific prestation by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new prestation
     */
  public function makeStore(array $data): Prestation
{


    // Création de l'utilisateur
    $prestation = Prestation::create($data);

    return $prestation;
}


    /**
     * Update an existing prestation
     */
  public function makeUpdate($id, array $data): Prestation
{
    $model = Prestation::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a prestation
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest prestations
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
     * Search for prestations by name, email, or code
     */
    public function search($term)
    {
        $query = Prestation::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
