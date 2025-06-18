<?php

namespace App\Http\Repositories;

use App\Models\AutorisationLicenciement;
use App\Traits\Repository;

class AutorisationLicenciementRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AutorisationLicenciement
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AutorisationLicenciement::class);
    }

    /**
     * Check if autorisationlicenciement exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all autorisationlicenciements with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AutorisationLicenciement::ignoreRequest(['per_page'])
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
     * Get a specific autorisationlicenciement by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new autorisationlicenciement
     */
  public function makeStore(array $data): AutorisationLicenciement
{


    // Création de l'utilisateur
    $autorisationlicenciement = AutorisationLicenciement::create($data);

    return $autorisationlicenciement;
}


    /**
     * Update an existing autorisationlicenciement
     */
  public function makeUpdate($id, array $data): AutorisationLicenciement
{
    $model = AutorisationLicenciement::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a autorisationlicenciement
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest autorisationlicenciements
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
     * Search for autorisationlicenciements by name, email, or code
     */
    public function search($term)
    {
        $query = AutorisationLicenciement::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
