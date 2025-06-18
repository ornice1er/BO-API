<?php

namespace App\Http\Repositories;

use App\Models\AgrementMedecine;
use App\Traits\Repository;

class AgrementMedecineRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AgrementMedecine
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AgrementMedecine::class);
    }

    /**
     * Check if agrementmedecine exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all agrementmedecines with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AgrementMedecine::ignoreRequest(['per_page'])
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
     * Get a specific agrementmedecine by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new agrementmedecine
     */
  public function makeStore(array $data): AgrementMedecine
{


    // Création de l'utilisateur
    $agrementmedecine = AgrementMedecine::create($data);

    return $agrementmedecine;
}


    /**
     * Update an existing agrementmedecine
     */
  public function makeUpdate($id, array $data): AgrementMedecine
{
    $model = AgrementMedecine::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a agrementmedecine
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest agrementmedecines
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
     * Search for agrementmedecines by name, email, or code
     */
    public function search($term)
    {
        $query = AgrementMedecine::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
