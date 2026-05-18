<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EtapeVisibiliteRepository;
use App\Http\Requests\EtapeVisibilite\StoreEtapeVisibiliteRequest;
use App\Http\Requests\EtapeVisibilite\UpdateEtapeVisibiliteRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class EtapeVisibiliteController extends Controller
{
    protected $repository;
    protected $ls;

    public function __construct(EtapeVisibiliteRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    public function index(Request $request)
    {
        $message = 'Récupération des règles de visibilité';

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
        $message = 'Récupération d\'une règle de visibilité';

        try {
            $result = $this->repository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function store(StoreEtapeVisibiliteRequest $request)
    {
        $message = 'Enregistrement d\'une règle de visibilité';

        try {
            $result = $this->repository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Règle de visibilité créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function update(UpdateEtapeVisibiliteRequest $request, $id)
    {
        $message = 'Mise à jour d\'une règle de visibilité';

        try {
            $result = $this->repository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Règle de visibilité mise à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'une règle de visibilité';

        try {
            $recup = $this->repository->get($id);
            $result = $this->repository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Règle de visibilité supprimée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function search(Request $request)
    {
        $message = 'Recherche de règles de visibilité';

        try {
            $result = $this->repository->search($request->search);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function destroyByPrestation(int $prestationId)
    {
        $message = 'Suppression des règles de visibilité d\'une prestation';
        try {
            $count = $this->repository->destroyByPrestation($prestationId);
            $this->ls->trace(['action_name' => $message, 'description' => "prestation_id=$prestationId, deleted=$count"]);

            return Common::success("$count règle(s) supprimée(s) avec succès", []);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function copyFromPrestation(Request $request)
    {
        $request->validate([
            'from_prestation_id' => 'required|integer|exists:prestations,id',
            'to_prestation_id'   => 'required|integer|exists:prestations,id|different:from_prestation_id',
        ]);

        $message = 'Copie des règles de visibilité';
        try {
            $count = $this->repository->copyFromPrestation(
                $request->from_prestation_id,
                $request->to_prestation_id
            );
            $this->ls->trace(['action_name' => $message, 'description' => "from={$request->from_prestation_id} to={$request->to_prestation_id}, copied=$count"]);

            return Common::success("$count règle(s) copiée(s) avec succès", []);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
