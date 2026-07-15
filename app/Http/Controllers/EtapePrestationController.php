<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EtapePrestationRepository;
use App\Http\Requests\EtapePrestation\StoreEtapePrestationRequest;
use App\Http\Requests\EtapePrestation\UpdateEtapePrestationRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

/**
 * Configuration des étapes par prestation.
 *
 * Les étapes sont globales ; ce contrôleur gère leur contextualisation
 * (SLA, unité responsable, RDV, association à une session) e-service par e-service.
 */
class EtapePrestationController extends Controller
{
    protected $repository;
    protected $ls;

    public function __construct(EtapePrestationRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    public function index(Request $request)
    {
        $message = 'Récupération des étapes par prestation';

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
        $message = 'Récupération d\'une étape de prestation';

        try {
            $result = $this->repository->get($id);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function store(StoreEtapePrestationRequest $request)
    {
        $message = 'Enregistrement d\'une étape de prestation';

        try {
            $result = $this->repository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Étape configurée avec succès pour cette prestation', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function update(UpdateEtapePrestationRequest $request, $id)
    {
        $message = 'Mise à jour d\'une étape de prestation';

        try {
            $result = $this->repository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Étape mise à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'une étape de prestation';

        try {
            $recup  = $this->repository->get($id);
            $result = $this->repository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Configuration supprimée — l\'étape reprend ses valeurs par défaut', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function search(Request $request)
    {
        $message = 'Recherche d\'étapes par prestation';

        try {
            $result = $this->repository->search($request->search);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Copie la contextualisation des étapes d'une prestation vers d'autres.
     * POST /api/etape-prestations/copy-from-prestation
     */
    public function copyFromPrestation(Request $request)
    {
        $message = 'Copie des étapes d\'une prestation';

        $request->validate([
            'from_prestation_id' => 'required|integer|exists:prestations,id',
            'to_prestation_ids'  => 'required|array|min:1',
            'to_prestation_ids.*' => 'integer|exists:prestations,id',
        ]);

        try {
            $count = $this->repository->copyFromPrestation(
                (int) $request->input('from_prestation_id'),
                $request->input('to_prestation_ids')
            );
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success("$count configuration(s) copiée(s) avec succès", ['count' => $count]);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Étapes du graphe d'une prestation, avec leurs valeurs effectives.
     * GET /api/etape-prestations/graphe/{prestationId}
     */
    public function graphe($prestationId)
    {
        $message = 'Étapes du graphe d\'une prestation';

        try {
            $result = $this->repository->etapesDuGraphe((int) $prestationId);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
