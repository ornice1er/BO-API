<?php

namespace App\Http\Repositories;

use App\Models\AttestationDeServiceFait;
use App\Traits\Repository;

class AttestationDeServiceFaitRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AttestationDeServiceFait
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AttestationDeServiceFait::class);
    }

    /**
     * Check if attestationdeservicefait exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all attestationdeservicefaits with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AttestationDeServiceFait::ignoreRequest(['per_page'])
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
     * Get a specific attestationdeservicefait by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new attestationdeservicefait
     */
  public function makeStore(array $data): AttestationDeServiceFait
{


    // Création de l'utilisateur
    $attestationdeservicefait = AttestationDeServiceFait::create($data);

    return $attestationdeservicefait;
}


    /**
     * Update an existing attestationdeservicefait
     */
  public function makeUpdate($id, array $data): AttestationDeServiceFait
{
    $model = AttestationDeServiceFait::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a attestationdeservicefait
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest attestationdeservicefaits
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
     * Search for attestationdeservicefaits by name, email, or code
     */
    public function search($term)
    {
        $query = AttestationDeServiceFait::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
