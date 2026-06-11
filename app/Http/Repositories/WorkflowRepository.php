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

    public function copyFromPrestation(int $fromId, int $toId): int
    {
        // Supprimer les transitions existantes de la cible (logs et visibilités compris)
        $this->destroyByPrestation($toId);

        $transitions = WorkflowTransition::where('prestation_id', $fromId)->get();

        foreach ($transitions as $t) {
            $copy = $t->replicate();
            $copy->prestation_id = $toId;
            $copy->save();
        }

        return $transitions->count();
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
        // Table relationnelle (prestation_id, etape_id) : recherche via les relations
        return Workflow::with(['prestation', 'etape'])
            ->whereHas('prestation', fn($q) =>
                $q->where('name', 'like', '%'.$term.'%')
                  ->orWhere('code', 'like', '%'.$term.'%'))
            ->orWhereHas('etape', fn($q) =>
                $q->where('name', 'like', '%'.$term.'%'))
            ->get();
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