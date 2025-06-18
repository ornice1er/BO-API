<?php

namespace App\Http\Repositories;

use App\Models\AutorisationDeStage;
use App\Traits\Repository;

class AutorisationDeStageRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var AutorisationDeStage
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(AutorisationDeStage::class);
    }

    /**
     * Check if autorisationdestage exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all autorisationdestages with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = AutorisationDeStage::ignoreRequest(['per_page'])
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
     * Get a specific autorisationdestage by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new autorisationdestage
     */
  public function makeStore(array $data): AutorisationDeStage
{


    // Création de l'utilisateur
    $autorisationdestage = AutorisationDeStage::create($data);

    return $autorisationdestage;
}


    /**
     * Update an existing autorisationdestage
     */
  public function makeUpdate($id, array $data): AutorisationDeStage
{
    $model = AutorisationDeStage::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a autorisationdestage
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest autorisationdestages
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
     * Search for autorisationdestages by name, email, or code
     */
    public function search($term)
    {
        $query = AutorisationDeStage::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
