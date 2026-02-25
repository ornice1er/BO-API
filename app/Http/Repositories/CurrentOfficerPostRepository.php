<?php

namespace App\Http\Repositories;

use App\Models\CurrentOfficerPost;
use App\Traits\Repository;

class CurrentOfficerPostRepository
{
    use Repository;

    /**
     * @var CurrentOfficerPost
     */
    protected $model;

    public function __construct()
    {
        $this->model = app(CurrentOfficerPost::class);
    }

    public function ifExist($id)
    {
        return $this->find($id);
    }

    public function getAll($request)
    {
        $per_page = 10;

        $req = CurrentOfficerPost::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with(['agent', 'uniteAdmin', 'fonction'])
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return $req->paginate($per_page);
        }

        return $req->get();
    }

    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Create a new CurrentOfficerPost with constraints:
     * - An agent can have only one active post
     * - A post (unit + function) can be occupied by only one active agent
     */
    public function makeStore(array $data): CurrentOfficerPost
    {
        // Normalize statut
        $statut = strtolower($data['statut'] ?? '');

        if ($statut === 'active') {
            // Ensure agent has no other active post
            $existsForAgent = CurrentOfficerPost::where('agent_id', $data['agent_id'])
                ->whereRaw('LOWER(statut) = ?', ['active'])
                ->exists();
            if ($existsForAgent) {
                abort(422, 'Cet agent a déjà un poste actif.');
            }

            // Ensure post not occupied by other active agent
            $occupied = CurrentOfficerPost::where('unite_admin_id', $data['unite_admin_id'])
                ->where('fonction_id', $data['fonction_id'])
                ->whereRaw('LOWER(statut) = ?', ['active'])
                ->exists();
            if ($occupied) {
                abort(422, 'Ce poste est déjà occupé par un autre agent.');
            }
        }

        return CurrentOfficerPost::create($data);
    }

    /**
     * Update an existing CurrentOfficerPost enforcing constraints on activation.
     */
    public function makeUpdate($id, array $data): CurrentOfficerPost
    {
        $model = CurrentOfficerPost::findOrFail($id);

        $newStatut = array_key_exists('statut', $data) ? strtolower($data['statut']) : strtolower($model->statut);
        $agentId = $data['agent_id'] ?? $model->agent_id;
        $uniteId = $data['unite_admin_id'] ?? $model->unite_admin_id;
        $fonctionId = $data['fonction_id'] ?? $model->fonction_id;

        if ($newStatut === 'active') {
            $existsForAgent = CurrentOfficerPost::where('agent_id', $agentId)
                ->whereRaw('LOWER(statut) = ?', ['active'])
                ->where('id', '!=', $model->id)
                ->exists();
            if ($existsForAgent) {
                abort(422, 'Cet agent a déjà un poste actif.');
            }

            $occupied = CurrentOfficerPost::where('unite_admin_id', $uniteId)
                ->where('fonction_id', $fonctionId)
                ->whereRaw('LOWER(statut) = ?', ['active'])
                ->where('id', '!=', $model->id)
                ->exists();
            if ($occupied) {
                abort(422, 'Ce poste est déjà occupé par un autre agent.');
            }
        }

        $model->update($data);
        return $model;
    }

    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Change statut (active/inactive)
     */
    public function setStatus($id, $status)
    {
        $model = $this->findOrFail($id);
        $new = strtolower($status) === '1' || strtolower($status) === 'active' ? 'active' : 'inactive';

        // Enforce constraints only when activating
        if ($new === 'active') {
            $existsForAgent = CurrentOfficerPost::where('agent_id', $model->agent_id)
                ->whereRaw('LOWER(statut) = ?', ['active'])
                ->where('id', '!=', $model->id)
                ->exists();
            if ($existsForAgent) {
                abort(422, 'Cet agent a déjà un poste actif.');
            }

            $occupied = CurrentOfficerPost::where('unite_admin_id', $model->unite_admin_id)
                ->where('fonction_id', $model->fonction_id)
                ->whereRaw('LOWER(statut) = ?', ['active'])
                ->where('id', '!=', $model->id)
                ->exists();
            if ($occupied) {
                abort(422, 'Ce poste est déjà occupé par un autre agent.');
            }
        }

        return $model->update(['statut' => $new]);
    }
}


