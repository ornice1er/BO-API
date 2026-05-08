<?php

namespace App\Http\Repositories;

use App\Models\WorkflowTransition;
use App\Models\Workflow;
use App\Models\RequeteEtapeLog;
use App\Models\EtapeVisibilite;

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

    public function __construct()
    {
        $this->model = app(WorkflowTransition::class);
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

        $req = WorkflowTransition::with(['prestation', 'etapeFrom', 'etapeTo', 'statusResult'])->orderBy('order');

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
    public function makeStore($data): WorkflowTransition
    {
        $model = new WorkflowTransition($data);
        $model->save();

        return $model->load(['prestation', 'etapeFrom', 'etapeTo', 'statusResult']);
    }

    public function makeUpdate($id, $data): WorkflowTransition
    {
        $model = WorkflowTransition::findOrFail($id);
        $model->update($data);

        return $model->load(['prestation', 'etapeFrom', 'etapeTo', 'statusResult']);
    }

    /**
     * To delete model
     */
    public function makeDestroy($id)
    {
        RequeteEtapeLog::where('workflow_transition_id', $id)->delete();
        EtapeVisibilite::where('workflow_transition_id', $id)->delete();
        return WorkflowTransition::findOrFail($id)->delete();
    }

    /**
     * Delete all transitions for a given prestation
     */
    public function destroyByPrestation($prestationId): int
    {
        $ids = WorkflowTransition::where('prestation_id', $prestationId)->pluck('id');
        RequeteEtapeLog::whereIn('workflow_transition_id', $ids)->delete();
        EtapeVisibilite::whereIn('workflow_transition_id', $ids)->delete();
        return WorkflowTransition::where('prestation_id', $prestationId)->delete();
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