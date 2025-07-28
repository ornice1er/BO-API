<?php

namespace App\Http\Repositories;

use App\Models\UniteAdmin;
use App\Traits\Repository;
use Auth;

class UniteAdminRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var UniteAdmin
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(UniteAdmin::class);
    }

    /**
     * Check if uniteadmincontroller exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all uniteadmincontrollers with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = UniteAdmin::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with('parent')
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    /**
     * Get a specific uniteadmincontroller by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new uniteadmincontroller
     */
  public function makeStore(array $data): UniteAdmin
{


    // Création de l'utilisateur
    $uniteadmincontroller = UniteAdmin::create($data);

    return $uniteadmincontroller;
}


    /**
     * Update an existing uniteadmincontroller
     */
  public function makeUpdate($id, array $data): UniteAdmin
{
    $model = UniteAdmin::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}

function principal() {
    
                $unite_admin = UniteAdmin::where('ua_parent_code', null)->orderBy('libelle', 'asc')->get();
    return   $unite_admin;
}
function collabs() {
    
$unite_admin = UniteAdmin::with("department")->where('ua_parent_code', Auth::user()->agent->uniteAdmin->id)->orderBy('libelle', 'asc')->get();
    return   $unite_admin;
}


    /**
     * Delete a uniteadmincontroller
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest uniteadmincontrollers
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
     * Search for uniteadmincontrollers by name, email, or code
     */
    public function search($term)
    {
        $query = UniteAdmin::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
