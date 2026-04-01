<?php

namespace App\Http\Repositories;

use App\Models\Workflow;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use Str;

class WorkflowRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Workflow
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Workflow::class);
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

        $req = Workflow::with(['prestation','etape']) ;

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
    public function makeStore($data): Workflow
    {

        if (request()->hasFile('file')) {
            $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['filename'] = 'projects/'.$filename;
        }
        unset( $data['file']);

        $model = new Workflow($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): Workflow
    {

        $model = Workflow::findOrFail($id);

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
    public function setWorkflow($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    public function search($term)
    {
        $query = Workflow::query(); // Commencer avec une requête vide
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
        return Workflow::with(['requetes.prestation','requetes.lastReponse','requetes.project'])->findOrFail($id);
    }

    /**
     * Add request IDs to project
     */
    public function addRequests($projectId, $requestIds)
    {
        $project = Workflow::findOrFail($projectId);
        $project->requests()->syncWithoutDetaching($requestIds);
        return $project->load('requests');
    }

    /**
     * Close project
     */
    public function close($id)
    {
        $project = Workflow::findOrFail($id);
        $project->update(['status' => 'closed']);
        return $project;
    }
}