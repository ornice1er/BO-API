<?php

namespace App\Http\Repositories;

use App\Models\Agenda;
use App\Traits\Repository;

class AgendaRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Agenda
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Agenda::class);
    }

    /**
     * Check if agenda exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all agendas with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Agenda::ignoreRequest(['per_page'])
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
     * Get a specific agenda by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new agenda
     */
  public function makeStore(array $data): Agenda
{


    // Création de l'utilisateur
    $agenda = Agenda::create($data);

    return $agenda;
}


    /**
     * Update an existing agenda
     */
  public function makeUpdate($id, array $data): Agenda
{
    $model = Agenda::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a agenda
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest agendas
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
     * Search for agendas by name, email, or code
     */
    public function search($term)
    {
        $query = Agenda::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
