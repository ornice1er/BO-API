<?php

namespace App\Http\Repositories;

use App\Models\Project;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use Str;

class ProjectRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Project
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Project::class);
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

        $req = Project::ignoreRequest(['per_page'])
            ->orderByDesc('created_at');

        if ($request->has('prestation_codes')) {
            $codes = array_map('trim', explode(',', $request->get('prestation_codes')));

            $req->where(function($q) use ($codes) {
                foreach ($codes as $code) {
                    $q->orWhereJsonContains('prestations', $code);
                }
            });
        }

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
         return $this->with([
        'requetes.eps.ps.status'
    ])->findOrFail($id);
    }

    /**
     * To store model
     */
    public function makeStore($data): Project
    {

        if (request()->hasFile('file')) {
            $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['filename'] = 'projects/'.$filename;
                    unset( $data['file']);

        }

         if (request()->hasFile('closing_filename')) {
            $filename = FileStorage::setFile('public', request()->file('closing_filename'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['closing_filename'] = 'projects/'.$filename;

        }

        $model = new Project($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): Project
    {

        $model = Project::findOrFail($id);

        if (request()->hasFile('file')) {
            FileStorage::deleteFile('public', $model->filename, 'projects');
            $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['filename'] = 'projects/'.$filename;
                    unset( $data['file']);

        }
          if (request()->hasFile('closing_filename')) {
            FileStorage::deleteFile('public', $model->filename, 'projects');
            $filename = FileStorage::setFile('public', request()->file('closing_filename'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['closing_filename'] = 'projects/'.$filename;
                    

        }
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
    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    public function search($term)
    {
        $query = Project::query(); // Commencer avec une requête vide
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
        return Project::with(['requetes.prestation','requetes.lastReponse','requetes.project','requetes.eps.ps.status'])->findOrFail($id);
    }

    /**
     * Add request IDs to project
     */
    public function addRequests($projectId, $requestIds)
    {
        $project = Project::findOrFail($projectId);
        $project->requests()->syncWithoutDetaching($requestIds);
        return $project->load('requests');
    }

    /**
     * Close project
     */
    public function close($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['status' => 'closed']);
        return $project;
    }
}