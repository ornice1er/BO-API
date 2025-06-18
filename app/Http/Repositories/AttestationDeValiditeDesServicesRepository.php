<?php

namespace App\Http\Repositories;

use App\Models\AttestationDeValiditeDesServices;
use App\Traits\Repository;

class AttestationDeValiditeDesServicesRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AttestationDeValiditeDesServices
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AttestationDeValiditeDesServices::class);
    }

    /**
     * Check if attestationdevaliditedesservices exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all attestationdevaliditedesservicess with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AttestationDeValiditeDesServices::ignoreRequest(['per_page'])
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
     * Get a specific attestationdevaliditedesservices by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new attestationdevaliditedesservices
     */
  public function makeStore(array $data): AttestationDeValiditeDesServices
{


    // Création de l'utilisateur
    $attestationdevaliditedesservices = AttestationDeValiditeDesServices::create($data);

    return $attestationdevaliditedesservices;
}


    /**
     * Update an existing attestationdevaliditedesservices
     */
  public function makeUpdate($id, array $data): AttestationDeValiditeDesServices
{
    $model = AttestationDeValiditeDesServices::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a attestationdevaliditedesservices
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest attestationdevaliditedesservicess
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
     * Search for attestationdevaliditedesservicess by name, email, or code
     */
    public function search($term)
    {
        $query = AttestationDeValiditeDesServices::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
