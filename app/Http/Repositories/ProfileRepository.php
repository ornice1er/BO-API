<?php

namespace App\Http\Repositories;

use App\Models\Profile;
use App\Traits\Repository;

class ProfileRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Profile
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Profile::class);
    }

    /**
     * Check if profile exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all profiles with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Profile::ignoreRequest(['per_page'])
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
     * Get a specific profile by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new profile
     */
  public function makeStore(array $data): Profile
{


    // Création de l'utilisateur
    $profile = Profile::create($data);

    return $profile;
}


    /**
     * Update an existing profile
     */
  public function makeUpdate($id, array $data): Profile
{
    $model = Profile::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a profile
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest profiles
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
     * Search for profiles by name, email, or code
     */
    public function search($term)
    {
        $query = Profile::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
