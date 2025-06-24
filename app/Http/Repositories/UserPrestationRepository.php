<?php

namespace App\Http\Repositories;

use App\Models\UserPrestation;
use App\Traits\Repository;

class UserPrestationRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var UserPrestation
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(UserPrestation::class);
    }

    /**
     * Check if UserPrestation exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all UserPrestations with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = UserPrestation::ignoreRequest(['per_page'])
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
     * Get a specific UserPrestation by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new UserPrestation
     */
  public function makeStore(array $data): UserPrestation
{


    // Création de l'utilisateur
    $UserPrestation = UserPrestation::create($data);

    return $UserPrestation;
}


    /**
     * Update an existing UserPrestation
     */
  public function makeUpdate($id, array $data): UserPrestation
{
    $model = UserPrestation::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a UserPrestation
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest UserPrestations
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
     * Search for UserPrestations by name, email, or code
     */
    public function search($term)
    {
        $query = UserPrestation::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}

