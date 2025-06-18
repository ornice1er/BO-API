<?php

namespace App\Http\Repositories;

use App\Models\AttestationExistence;
use App\Traits\Repository;

class AttestationExistenceRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AttestationExistence
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AttestationExistence::class);
    }

    /**
     * Check if attestationexistence exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all attestationexistences with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AttestationExistence::ignoreRequest(['per_page'])
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
     * Get a specific attestationexistence by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new attestationexistence
     */
  public function makeStore(array $data): AttestationExistence
{


    // Création de l'utilisateur
    $attestationexistence = AttestationExistence::create($data);

    return $attestationexistence;
}


    /**
     * Update an existing attestationexistence
     */
  public function makeUpdate($id, array $data): AttestationExistence
{
    $model = AttestationExistence::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a attestationexistence
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest attestationexistences
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
     * Search for attestationexistences by name, email, or code
     */
    public function search($term)
    {
        $query = AttestationExistence::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
