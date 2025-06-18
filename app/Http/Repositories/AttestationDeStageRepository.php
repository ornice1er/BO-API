<?php

namespace App\Http\Repositories;

use App\Models\AttestationDeStage;
use App\Traits\Repository;

class AttestationDeStageRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AttestationDeStage
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AttestationDeStage::class);
    }

    /**
     * Check if attestationdestage exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all attestationdestages with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AttestationDeStage::ignoreRequest(['per_page'])
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
     * Get a specific attestationdestage by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new attestationdestage
     */
  public function makeStore(array $data): AttestationDeStage
{


    // Création de l'utilisateur
    $attestationdestage = AttestationDeStage::create($data);

    return $attestationdestage;
}


    /**
     * Update an existing attestationdestage
     */
  public function makeUpdate($id, array $data): AttestationDeStage
{
    $model = AttestationDeStage::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a attestationdestage
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest attestationdestages
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
     * Search for attestationdestages by name, email, or code
     */
    public function search($term)
    {
        $query = AttestationDeStage::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
