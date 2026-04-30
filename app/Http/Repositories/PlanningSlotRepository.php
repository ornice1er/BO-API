<?php

namespace App\Http\Repositories;

use App\Models\PlanningSlot;
use App\Models\Prestation;
use App\Traits\Repository;

class PlanningSlotRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(PlanningSlot::class);
    }

    public function getAll($request)
    {
        $query = PlanningSlot::with(['uniteAdmin:id,libelle', 'prestation:id,name,code'])
            ->orderBy('slot_date')
            ->orderBy('heure_debut');

        if ($request->filled('unite_admin_id')) {
            $query->where('unite_admin_id', $request->unite_admin_id);
        }

        if ($request->filled('prestation_id')) {
            $query->where('prestation_id', $request->prestation_id);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('slot_date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('slot_date', '<=', $request->date_end);
        }

        if ($request->filled('session_type')) {
            $query->where('session_type', $request->session_type);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', (bool) $request->is_available);
        }

        $per_page = $request->get('per_page', 20);

        return $query->paginate($per_page);
    }

    public function get($id)
    {
        return PlanningSlot::with(['uniteAdmin:id,libelle', 'prestation:id,name,code'])
            ->findOrFail($id);
    }

    public function makeStore(array $data): PlanningSlot
    {
        return PlanningSlot::create($data);
    }

    public function makeUpdate($id, array $data): PlanningSlot
    {
        $slot = PlanningSlot::findOrFail($id);
        $slot->update($data);
        return $slot->fresh(['uniteAdmin:id,libelle', 'prestation:id,name,code']);
    }

    public function makeDestroy($id): bool
    {
        return PlanningSlot::findOrFail($id)->delete();
    }

    public function toggleAvailability($id): PlanningSlot
    {
        $slot = PlanningSlot::findOrFail($id);
        $slot->update(['is_available' => !$slot->is_available]);
        return $slot;
    }
}
