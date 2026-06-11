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

        $req = PrestationStatus::with('prestation', 'status');

        if ($request->filled('prestation_id')) {
            $req->where('prestation_id', (int) $request->prestation_id);
        }

        if (array_key_exists('per_page', $request->all())) {
            return $req->paginate((int) $request->per_page);
        }

        return $req->get();
    }

    /**
     * Get an element
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * To store model — crée une ligne par status_id sélectionné
     */
    public function makeStore($data): array
    {
        $created = [];
        foreach ($data['status_ids'] as $statusId) {
            $model = PrestationStatus::firstOrCreate([
                'prestation_id' => $data['prestation_id'],
                'status_id'     => $statusId,
            ]);
            $created[] = $model;
        }

        return $created;
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
        // Table pivot (status_id, prestation_id) : recherche via les relations
        return PrestationStatus::with(['status', 'prestation'])
            ->whereHas('status', fn($q) =>
                $q->where('name', 'like', '%'.$term.'%')
                  ->orWhere('short_name', 'like', '%'.$term.'%'))
            ->orWhereHas('prestation', fn($q) =>
                $q->where('name', 'like', '%'.$term.'%')
                  ->orWhere('code', 'like', '%'.$term.'%'))
            ->get();
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