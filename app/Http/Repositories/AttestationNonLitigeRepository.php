<?php

namespace App\Http\Repositories;

use App\Models\AttestationNonLitige;
use App\Traits\Repository;

class AttestationNonLitigeRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AttestationNonLitige
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AttestationNonLitige::class);
    }

    /**
     * Check if attestationnonlitige exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all attestationnonlitiges with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AttestationNonLitige::ignoreRequest(['per_page'])
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
     * Get a specific attestationnonlitige by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new attestationnonlitige
     */
  public function makeStore(array $data): AttestationNonLitige
{


    // Création de l'utilisateur
    $attestationnonlitige = AttestationNonLitige::create($data);

    return $attestationnonlitige;
}


    /**
     * Update an existing attestationnonlitige
     */
  public function makeUpdate($id, array $data): AttestationNonLitige
{
    $model = AttestationNonLitige::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a attestationnonlitige
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest attestationnonlitiges
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
     * Search for attestationnonlitiges by name, email, or code
     */
    public function search($term)
    {
        $query = AttestationNonLitige::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
