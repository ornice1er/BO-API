<?php

namespace App\Http\Repositories;

use App\Models\EtudeDossier;
use App\Traits\Repository;

class EtudeDossierRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var EtudeDossier
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = app(EtudeDossier::class);
    }

    /**
     * Check if etude dossier exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all etude dossiers with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = EtudeDossier::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with(['commissionMember.member', 'commissionRequete.requete'])
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];
            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Get a specific etude dossier by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Store a new etude dossier
     */
    public function makeStore(array $data): EtudeDossier
    {
        return EtudeDossier::create($data);
    }

    /**
     * Update an existing etude dossier
     */
    public function makeUpdate($id, array $data): EtudeDossier
    {
        $model = EtudeDossier::findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * Delete an etude dossier
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest etude dossiers
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    /**
     * Set etude dossier status
     */
    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['status' => $status]);
    }

    /**
     * Search for etude dossiers
     */
    public function search($term)
    {
        $query = EtudeDossier::query();
        $attrs = ['status', 'comment'];

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get();
    }

    /**
     * Get etude dossiers by commission
     */
    public function getByCommission($commissionId)
    {
        return EtudeDossier::whereHas('commissionRequete', function($query) use ($commissionId) {
            $query->where('commission_id', $commissionId);
        })->with(['commissionMember.member', 'commissionRequete.requete'])->get();
    }

    /**
     * Get etude dossiers by member
     */
    public function getByMember($memberId)
    {
        return EtudeDossier::whereHas('commissionMember', function($query) use ($memberId) {
            $query->where('member_id', $memberId);
        })->with(['commissionRequete.requete', 'commissionMember.commission'])->get();
    }

    /**
     * Get etude dossiers by requete
     */
    public function getByRequete($requeteId)
    {
        return EtudeDossier::whereHas('commissionRequete', function($query) use ($requeteId) {
            $query->where('requete_id', $requeteId);
        })->with(['commissionMember.member', 'commissionRequete.commission'])->get();
    }

    /**
     * Get pending evaluations
     */
    public function getPending()
    {
        return EtudeDossier::pending()->with(['commissionMember.member', 'commissionRequete.requete'])->get();
    }

    /**
     * Get completed evaluations
     */
    public function getCompleted()
    {
        return EtudeDossier::completed()->with(['commissionMember.member', 'commissionRequete.requete'])->get();
    }

    /**
     * Get evaluations with marks
     */
    public function getWithMarks()
    {
        return EtudeDossier::withMark()->with(['commissionMember.member', 'commissionRequete.requete'])->get();
    }

    /**
     * Calculate average mark for a commission-requete
     */
    public function getAverageMark($commissionRequeteId)
    {
        return EtudeDossier::where('commission_requete_id', $commissionRequeteId)
            ->whereNotNull('mark')
            ->avg('mark');
    }

    /**
     * Get evaluation statistics
     */
    public function getStatistics($commissionId = null)
    {
        $query = EtudeDossier::query();
        
        if ($commissionId) {
            $query->whereHas('commissionRequete', function($q) use ($commissionId) {
                $q->where('commission_id', $commissionId);
            });
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->pending()->count(),
            'completed' => (clone $query)->completed()->count(),
            'with_marks' => (clone $query)->withMark()->count(),
            'average_mark' => (clone $query)->withMark()->avg('mark')
        ];
    }
} 