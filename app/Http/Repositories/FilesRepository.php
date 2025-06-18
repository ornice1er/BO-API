<?php

namespace App\Http\Repositories;

use App\Models\Files;
use App\Traits\Repository;

class FilesRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Files
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Files::class);
    }

    /**
     * Check if files exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all filess with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Files::ignoreRequest(['per_page'])
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
     * Get a specific files by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new files
     */
  public function makeStore(array $data): Files
{


    // Création de l'utilisateur
    $files = Files::create($data);

    return $files;
}


    /**
     * Update an existing files
     */
  public function makeUpdate($id, array $data): Files
{
    $model = Files::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a files
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest filess
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
     * Search for filess by name, email, or code
     */
    public function search($term)
    {
        $query = Files::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
