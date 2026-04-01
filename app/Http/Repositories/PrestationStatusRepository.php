<?php

namespace App\Http\Repositories;

use App\Models\PrestationStatus;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use Str;

class PrestationStatusRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var PrestationStatus
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(PrestationStatus::class);
    }

    /**
     * Check if exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all elements
     */
    public function getAll($request)
    {

        $per_page = 10;

        $req = PrestationStatus::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with('prestation','status')
            ;

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);

        } else {
            return $req->get();
        }
    }

    /**
     * Get an element
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * To store model
     */
    public function makeStore($data): PrestationStatus
    {

        if (request()->hasFile('file')) {
            $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['filename'] = 'projects/'.$filename;
        }
        unset( $data['file']);

        $model = new PrestationStatus($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): PrestationStatus
    {

        $model = PrestationStatus::findOrFail($id);

        if (request()->hasFile('file')) {
            FileStorage::deleteFile('public', $model->filename, 'projects');
            $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['filename'] = 'projects/'.$filename;
        }
        unset( $data['file']);
        $model->update($data);

        return $model;
    }

    /**
     * To delete model
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * To get all latest
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }

    /**
     * Get an element
     */
    public function setPrestationStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    public function search($term)
    {
        $query = PrestationStatus::query(); // Commencer avec une requête vide
        $attrs = ['title', 'description'];
        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Retourner les résultats
    }

    /**
     * Get project with requests
     */
    public function getWithRequests($id)
    {
        return PrestationStatus::with(['requetes.prestation','requetes.lastReponse','requetes.project'])->findOrFail($id);
    }

    /**
     * Add request IDs to project
     */
    public function addRequests($projectId, $requestIds)
    {
        $project = PrestationStatus::findOrFail($projectId);
        $project->requests()->syncWithoutDetaching($requestIds);
        return $project->load('requests');
    }

    /**
     * Close project
     */
    public function close($id)
    {
        $project = PrestationStatus::findOrFail($id);
        $project->update(['status' => 'closed']);
        return $project;
    }
}