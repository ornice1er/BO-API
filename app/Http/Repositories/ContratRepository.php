<?php

namespace App\Http\Repositories;

use App\Models\Contrat;
use App\Traits\Repository;

class ContratRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Contrat
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Contrat::class);
    }

    /**
     * Check if contrat exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all contrats with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Contrat::ignoreRequest(['per_page'])
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
     * Get a specific contrat by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new contrat
     */
  public function makeStore(array $data): Contrat
{


    // Création de l'utilisateur
    $contrat = Contrat::create($data);

    return $contrat;
}


    /**
     * Update an existing contrat
     */
  public function makeUpdate($id, array $data): Contrat
{
    $model = Contrat::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a contrat
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest contrats
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
     * Search for contrats by name, email, or code
     */
    public function search($term)
    {
        $query = Contrat::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
