<?php

namespace App\Http\Repositories;

use App\Models\Agent;
use App\Traits\Repository;

class AgentRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Agent
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Agent::class);
    }

    /**
     * Check if agent exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all agents with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Agent::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with(['FonctionAgent','uniteAdmin'])
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    /**
     * Get a specific agent by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new agent
     */
  public function makeStore(array $data): Agent
{


    // Création de l'utilisateur
    $agent = Agent::create($data);

    return $agent;
}


    /**
     * Update an existing agent
     */
  public function makeUpdate($id, array $data): Agent
{
    $model = Agent::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a agent
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest agents
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
     * Search for agents by name, email, or code
     */
    public function search($term)
    {
        $query = Agent::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}