<?php

namespace App\Http\Repositories;

use App\Models\Couverture;
use App\Traits\Repository;

class CouvertureRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Couverture
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Couverture::class);
    }

    /**
     * Check if couverture exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all couvertures with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Couverture::ignoreRequest(['per_page'])
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
     * Get a specific couverture by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new couverture
     */
  public function makeStore(array $data): Couverture
{


    // Création de l'utilisateur
    $couverture = Couverture::create($data);

    return $couverture;
}


    /**
     * Update an existing couverture
     */
  public function makeUpdate($id, array $data): Couverture
{
    $model = Couverture::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a couverture
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest couvertures
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
     * Search for couvertures by name, email, or code
     */
    public function search($term)
    {
        $query = Couverture::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
