<?php

namespace App\Http\Repositories;

use App\Events\ChangeStatutAgentEvent;
use App\Models\Setting;
use App\Models\EntiteAdmin;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class EntiteAdminRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var EntiteAdmin
     */
    protected $model;

    /**
     * The model being queried.
     *
     * @var EquipeService
     */
    protected $es;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(EntiteAdmin::class);
    }

    /**
     * Check if EntiteAdmin exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all EntiteAdmins with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = EntiteAdmin::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with('uas')
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    /**
     * Get a specific EntiteAdmin by id
     */
    public function get($id)
    {

        try {
            if (request()->has('project_id')) {
                return $this->whereHas('EntiteAdminProject', function ($q) {
                    $q->where('project_id', request()->project_id);
                })->findOrFail($id)->load('EntiteAdminProject.roles');
            } else {
                return $this->findOrFail($id);

            }
        } catch (\Throwable $th) {
            info($th->getMessage());

            return null;
        }

        
    }



    /**
     * Store a new EntiteAdmin
     */
    public function makeStore($data): EntiteAdmin
    {

        $model = new EntiteAdmin($data);
        $model->save();

        return $model;
    }

    /**
     * Update an existing EntiteAdmin
     */
    public function makeUpdate($id, $data): EntiteAdmin
    {
        $model = EntiteAdmin::findOrFail($id);
                $model->update($data);


        return $model;
    }

    /**
     * Delete a EntiteAdmin
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest EntiteAdmins
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
     * Search for EntiteAdmins by name, email, or code
     */
    public function search($term)
    {
        $query = EntiteAdmin::query(); // Start with an empty query
        $attrs = ['name', 'email', 'code']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
