<?php

namespace App\Http\Repositories;

use App\Models\ContratFile;
use App\Traits\Repository;

class ContratFileRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var ContratFile
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(ContratFile::class);
    }

    /**
     * Check if ContratFile exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all ContratFiles with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = ContratFile::ignoreRequest(['per_page'])
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
     * Get a specific ContratFile by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new ContratFile
     */
  public function makeStore(array $data): ContratFile
{


    // Création de l'utilisateur
    $ContratFile = ContratFile::create($data);

    return $ContratFile;
}


    /**
     * Update an existing ContratFile
     */
  public function makeUpdate($id, array $data): ContratFile
{
    $model = ContratFile::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a ContratFile
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest ContratFiles
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
     * Search for ContratFiles by name, email, or code
     */
    public function search($term)
    {
        $query = ContratFile::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
