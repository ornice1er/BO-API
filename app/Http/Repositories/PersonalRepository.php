<?php

namespace App\Http\Repositories;

use App\Models\Personal;
use App\Traits\Repository;

class PersonalRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Personal
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Personal::class);
    }

    /**
     * Check if personal exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all personals with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Personal::ignoreRequest(['per_page'])
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
     * Get a specific personal by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new personal
     */
  public function makeStore(array $data): Personal
{


    // Création de l'utilisateur
    $personal = Personal::create($data);

    return $personal;
}


    /**
     * Update an existing personal
     */
  public function makeUpdate($id, array $data): Personal
{
    $model = Personal::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a personal
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest personals
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
     * Search for personals by name, email, or code
     */
    public function search($term)
    {
        $query = Personal::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
