<?php

namespace App\Http\Repositories;

use App\Models\Pacours;
use App\Traits\Repository;

class PacoursRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Pacours
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Pacours::class);
    }

    /**
     * Check if pacours exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all pacourss with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Pacours::ignoreRequest(['per_page'])
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
     * Get a specific pacours by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new pacours
     */
  public function makeStore(array $data): Pacours
{


    // Création de l'utilisateur
    $pacours = Pacours::create($data);

    return $pacours;
}


    /**
     * Update an existing pacours
     */
  public function makeUpdate($id, array $data): Pacours
{
    $model = Pacours::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a pacours
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest pacourss
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
     * Search for pacourss by name, email, or code
     */
    public function search($term)
    {
        $query = Pacours::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
