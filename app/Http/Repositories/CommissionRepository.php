<?php

namespace App\Http\Repositories;

use App\Models\Commission;
use App\Traits\Repository;

class CommissionRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Commission
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = app(Commission::class);
    }

    /**
     * Check if commission exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all commissions with filtering, pagination, and sorting
     */


    public function getAll($request)
    {
         $per_page = 10;

         $filters = collect($request->all())
            ->except(['per_page', 'page'])
            ->toArray();

         $query = Commission::filter($filters)
            ->orderByDesc('created_at');

        return array_key_exists('per_page', $request->all())
            ? $query->paginate($request['per_page'])
            : $query->get();
    }

    /**
     * Get a specific commission by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Store a new commission
     */
    public function makeStore(array $data): Commission
    {
        return Commission::create($data);
    }

    /**
     * Update an existing commission
     */
    public function makeUpdate($id, array $data): Commission
    {
        $model = Commission::findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * Delete a commission
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest commissions
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    /**
     * Set commission status
     */
    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['status' => $status]);
    }

    /**
     * Search for commissions by name or responsable
     */
    public function search($term)
    {
        $query = Commission::query();
        $attrs = ['name', 'responsable'];

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get();
    }

    /**
     * Get active commissions
     */
    public function getActive()
    {
        return Commission::active()->get();
    }

    /**
     * Get closed commissions
     */
    public function getClosed()
    {
        return Commission::closed()->get();
    }

    /**
     * Get commission with members
     */
    public function getWithMembers($id)
    {
        return Commission::with(['members' => function($query) {
            $query->wherePivot('is_active', true);
        }])->findOrFail($id);
    }

    /**
     * Get commission with requetes
     */
    public function getWithRequetes($id)
    {
        return Commission::with(['requetes'])->findOrFail($id);
    }

    /**
     * Get commission with full details
     */
    public function getWithFullDetails($id)
    {
        return Commission::with([
            'members' => function($query) {
                $query->wherePivot('is_active', true);
            },
            'requetes',
            'commissionRequetes.etudeDossiers.commissionMember.member'
        ])->findOrFail($id);
    }

    /**
     * Add members to commission
     */
    public function addMembers($commissionId, array $memberIds, $isActive = true)
    {
        $commission = Commission::findOrFail($commissionId);
        $commission->members()->attach($memberIds, ['is_active' => $isActive]);
        
        $commission->load('members');
        return $commission;
    }

    /**
     * Remove members from commission
     */
    public function removeMembers($commissionId, array $memberIds)
    {
        $commission = Commission::findOrFail($commissionId);
        $commission->members()->detach($memberIds);
        return $commission;
    }

    /**
     * Add requetes to commission
     */
    public function addRequetes($commissionId, array $requeteIds, $status = 'pending')
    {
        $commission = Commission::findOrFail($commissionId);
        $commission->requetes()->attach($requeteIds, ['status' => $status]);
        return $commission;
    }

    /**
     * Remove requetes from commission
     */
    public function removeRequetes($commissionId, array $requeteIds)
    {
        $commission = Commission::findOrFail($commissionId);
        $commission->requetes()->detach($requeteIds);
        return $commission;
    }

    /**
     * Close commission
     */
    public function closeCommission($id)
    {
        return $this->setStatus($id, 'closed');
    }
} 