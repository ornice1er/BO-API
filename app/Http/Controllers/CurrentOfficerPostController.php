<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CurrentOfficerPostRepository;
use App\Http\Requests\CurrentOfficerPost\StoreCurrentOfficerPostRequest;
use App\Http\Requests\CurrentOfficerPost\UpdateCurrentOfficerPostRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class CurrentOfficerPostController extends Controller
{
    protected $repository;
    protected $ls;

    public function __construct(CurrentOfficerPostRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    public function index(Request $request)
    {
        $message = 'Récupération de la liste des postes courants';
        try {
            $result = $this->repository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function show($id)
    {
        $message = 'Récupération d\'un poste courant';
        try {
            $result = $this->repository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);
            return Common::success('Poste courant trouvé', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function store(StoreCurrentOfficerPostRequest $request)
    {
        $message = 'Enregistrement d\'un poste courant';
        try {
            $result = $this->repository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);
            return Common::successCreate('Poste courant créé', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function update(UpdateCurrentOfficerPostRequest $request, $id)
    {
        $message = 'Mise à jour d\'un poste courant';
        try {
            $result = $this->repository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);
            return Common::success('Poste courant mis à jour', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'un poste courant';
        try {
            $recup = $this->repository->get($id);
            $result = $this->repository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);
            return Common::successDelete('Poste courant supprimé', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function changeState($id, $state)
    {
        $message = 'Changement de l\'état d\'un poste courant';
        try {
            $result = $this->repository->setStatus($id, $state);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);
            $statusMessage = (strtolower((string) $state) === '1' || strtolower((string) $state) === 'active') ? 'activé' : 'désactivé';
            return Common::success("Poste courant $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }
}


