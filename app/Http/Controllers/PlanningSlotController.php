<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PlanningSlotRepository;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class PlanningSlotController extends Controller
{
    protected $repo;
    protected $ls;

    public function __construct(PlanningSlotRepository $repo, LogService $ls)
    {
        $this->repo = $repo;
        $this->ls = $ls;
    }

    public function index(Request $request)
    {
        $message = 'Récupération des créneaux de planning';
        try {
            $result = $this->repo->getAll($request);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function store(Request $request)
    {
        $message = 'Création d\'un créneau de planning';
        try {
            $result = $this->repo->makeStore($request->all());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);
            return Common::successCreate('Créneau créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function show($id)
    {
        $message = 'Récupération d\'un créneau';
        try {
            $result = $this->repo->get($id);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function update(Request $request, $id)
    {
        $message = 'Mise à jour d\'un créneau';
        try {
            $result = $this->repo->makeUpdate($id, $request->except('_method'));
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);
            return Common::success('Créneau mis à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'un créneau';
        try {
            $this->repo->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => "id=$id"]);
            return Common::successDelete('Créneau supprimé avec succès', null);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function toggleAvailability($id)
    {
        $message = 'Basculement de disponibilité d\'un créneau';
        try {
            $result = $this->repo->toggleAvailability($id);
            $this->ls->trace(['action_name' => $message, 'description' => "id=$id"]);
            $label = $result->is_available ? 'ouvert' : 'fermé';
            return Common::success("Créneau $label avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }
}
