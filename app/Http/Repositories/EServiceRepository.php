<?php

namespace App\Http\Repositories;

use App\Models\EService;
use App\Traits\Repository;

class EServiceRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var EService
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(EService::class);
    }

    /**
     * Check if eservice exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all eservices with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = EService::ignoreRequest(['per_page'])
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
     * Get a specific eservice by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new eservice
     */
  public function makeStore(array $data): EService
{


    // Création de l'utilisateur
    $eservice = EService::create($data);

    return $eservice;
}


    /**
     * Update an existing eservice
     */
  public function makeUpdate($id, array $data): EService
{
    $model = EService::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a eservice
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest eservices
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
     * Search for eservices by name, email, or code
     */
    public function search($term)
    {
        $query = EService::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
