<?php

namespace App\Http\Repositories;

use App\Models\File;
use App\Traits\Repository;

class FilesRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var File
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(File::class);
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

        $req = File::ignoreRequest(['per_page'])
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
  public function makeStore(array $data): File
{


    // Création de l'utilisateur
    $files = File::create($data);

    return $files;
}


    /**
     * Update an existing files
     */
  public function makeUpdate($id, array $data): File
{
    $model = File::findOrFail($id);



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
        $query = File::query(); // Start with an empty query
        $attrs = ['name', 'slug'];

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get();
    }
}
