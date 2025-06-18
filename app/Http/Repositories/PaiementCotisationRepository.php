<?php

namespace App\Http\Repositories;

use App\Models\PaiementCotisation;
use App\Traits\Repository;

class PaiementCotisationRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var PaiementCotisation
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(PaiementCotisation::class);
    }

    /**
     * Check if paiementcotisation exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all paiementcotisations with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = PaiementCotisation::ignoreRequest(['per_page'])
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
     * Get a specific paiementcotisation by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new paiementcotisation
     */
  public function makeStore(array $data): PaiementCotisation
{


    // Création de l'utilisateur
    $paiementcotisation = PaiementCotisation::create($data);

    return $paiementcotisation;
}


    /**
     * Update an existing paiementcotisation
     */
  public function makeUpdate($id, array $data): PaiementCotisation
{
    $model = PaiementCotisation::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a paiementcotisation
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest paiementcotisations
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
     * Search for paiementcotisations by name, email, or code
     */
    public function search($term)
    {
        $query = PaiementCotisation::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
