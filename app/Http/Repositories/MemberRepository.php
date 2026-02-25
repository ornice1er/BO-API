<?php

namespace App\Http\Repositories;

use App\Models\Member;
use App\Traits\Repository;

class MemberRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Member
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = app(Member::class);
    }

    /**
     * Check if member exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all members with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
         $per_page = 10;

         $filters = collect($request->all())
            ->except(['per_page', 'page'])
            ->toArray();

         $query = Member::filter($filters)
            ->orderByDesc('created_at');

        return array_key_exists('per_page', $request->all())
            ? $query->paginate($request['per_page'])
            : $query->get();
    }


    /**
     * Get a specific member by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Store a new member
     */
    public function makeStore(array $data): Member
    {
        return Member::create($data);
    }

    /**
     * Update an existing member
     */
    public function makeUpdate($id, array $data): Member
    {
        $model = Member::findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * Delete a member
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest members
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    /**
     * Set member status
     */
    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    /**
     * Search for members by name, email, or fonction
     */
    public function search($term)
    {
        $query = Member::query();
        $attrs = ['lastname', 'firstname', 'email', 'fonction'];

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get();
    }

    /**
     * Get active members
     */
    public function getActive()
    {
        return Member::where('is_active', true)->get();
    }

    /**
     * Get members with their commissions
     */
    public function getWithCommissions($id = null)
    {
        $query = Member::with(['commissions' => function($query) {
            $query->wherePivot('is_active', true);
        }]);

        if ($id) {
            return $query->findOrFail($id);
        }

        return $query->get();
    }
} 