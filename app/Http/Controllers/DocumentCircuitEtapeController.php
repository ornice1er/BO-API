<?php

namespace App\Http\Controllers;

use App\Http\Repositories\DocumentCircuitEtapeRepository;
use App\Http\Requests\DocumentCircuitEtape\StoreDocumentCircuitEtapeRequest;
use App\Http\Requests\DocumentCircuitEtape\UpdateDocumentCircuitEtapeRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class DocumentCircuitEtapeController extends Controller
{
    protected $repository;
    protected $ls;

    public function __construct(DocumentCircuitEtapeRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    public function index(Request $request)
    {
        $message = 'Récupération des étapes de circuit';

        try {
            $result = $this->repository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function show($id)
    {
        $message = 'Récupération d\'une étape de circuit';

        try {
            $result = $this->repository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function store(StoreDocumentCircuitEtapeRequest $request)
    {
        $message = 'Enregistrement d\'une étape de circuit';

        try {
            $result = $this->repository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Étape de circuit créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function update(UpdateDocumentCircuitEtapeRequest $request, $id)
    {
        $message = 'Mise à jour d\'une étape de circuit';

        try {
            $result = $this->repository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Étape de circuit mise à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'une étape de circuit';

        try {
            $recup = $this->repository->get($id);
            $result = $this->repository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Étape de circuit supprimée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function search(Request $request)
    {
        $message = 'Recherche d\'étapes de circuit';

        try {
            $result = $this->repository->search($request->search);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
