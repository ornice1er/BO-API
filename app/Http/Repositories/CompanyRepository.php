<?php

namespace App\Http\Repositories;

use App\Models\Company;
use App\Traits\Repository;

class CompanyRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Company
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Company::class);
    }

    /**
     * Check if company exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all companys with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Company::ignoreRequest(['per_page'])
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
     * Get a specific company by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new company
     */
  public function makeStore(array $data): Company
{


    // Création de l'utilisateur
    $company = Company::create($data);

    return $company;
}


    /**
     * Update an existing company
     */
  public function makeUpdate($id, array $data): Company
{
    $model = Company::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a company
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest companys
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
     * Search for companys by name, email, or code
     */
    public function search($term)
    {
        $query = Company::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
