<?php

namespace App\Http\Repositories;

use App\Models\StartPoint;
use App\Traits\Repository;

class StartPointRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var StartPoint
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(StartPoint::class);
    }

    /**
     * Check if startPoint exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all startPoints with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = StartPoint::ignoreRequest(['per_page'])
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
     * Get a specific startPoint by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new startPoint
     */
  public function makeStore(array $data): StartPoint
{


    // Création de l'utilisateur
    $startPoint = StartPoint::create($data);

    return $startPoint;
}


    /**
     * Update an existing startPoint
     */
  public function makeUpdate($id, array $data): StartPoint
{
    $model = StartPoint::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a startPoint
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest startPoints
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
     * Search for startPoints by name, email, or code
     */
    public function search($term)
    {
        $query = StartPoint::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
