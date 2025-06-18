<?php

namespace App\Http\Repositories;

use App\Models\CertificatDeNonRadiation;
use App\Traits\Repository;

class CertificatDeNonRadiationRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var CertificatDeNonRadiation
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(CertificatDeNonRadiation::class);
    }

    /**
     * Check if certificatdenonradiation exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all certificatdenonradiations with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = CertificatDeNonRadiation::ignoreRequest(['per_page'])
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
     * Get a specific certificatdenonradiation by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new certificatdenonradiation
     */
  public function makeStore(array $data): CertificatDeNonRadiation
{


    // Création de l'utilisateur
    $certificatdenonradiation = CertificatDeNonRadiation::create($data);

    return $certificatdenonradiation;
}


    /**
     * Update an existing certificatdenonradiation
     */
  public function makeUpdate($id, array $data): CertificatDeNonRadiation
{
    $model = CertificatDeNonRadiation::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a certificatdenonradiation
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest certificatdenonradiations
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
     * Search for certificatdenonradiations by name, email, or code
     */
    public function search($term)
    {
        $query = CertificatDeNonRadiation::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
