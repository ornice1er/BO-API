<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EtapePrestationStatusRepository;
use App\Http\Requests\EtapePrestationStatus\StoreEtapePrestationStatusRequest;
use App\Http\Requests\EtapePrestationStatus\UpdateEtapePrestationStatusRequest;
use App\Http\Requests\EtapePrestationStatus\AddRequestsToEtapePrestationStatusRequest;
use App\Jobs\CloseEtapePrestationStatusRequests;
use App\Models\EtapePrestationStatus;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EtapePrestationStatusController extends Controller
{
    /**
     * The etapePrestationStatus being queried.
     *
     * @var EtapePrestationStatus
     */
    protected $etapePrestationStatusRepository;

    protected $ls;

    public function __construct(EtapePrestationStatusRepository $etapePrestationStatusRepository, LogService $ls)
    {
        $this->etapePrestationStatusRepository = $etapePrestationStatusRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/etapePrestationStatuss",
     *      operationId="EtapePrestationStatus list",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Return EtapePrestationStatus data ",
     *      description="Get all etapePrestationStatuss",
     *
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Can used for filtering data by name",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/EtapePrestationStatus")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function index(Request $request)
    {
        $message = 'Récupération de la liste des projets';

        try {
            $result = $this->etapePrestationStatusRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etapePrestationStatuss/{id}",
     *      operationId="EtapePrestationStatus show",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Return one EtapePrestationStatus data with requests ",
     *      description="Get etapePrestationStatus by ID with associated requests",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/EtapePrestationStatus")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function show($id)
    {
        $message = 'Récupération d\'un projet avec ses requêtes';

        try {
            $result = $this->etapePrestationStatusRepository->getWithRequests($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Projet trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/etapePrestationStatuss",
     *      operationId="EtapePrestationStatus store",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="store EtapePrestationStatus data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/EtapePrestationStatus")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatusCreate")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function store(StoreEtapePrestationStatusRequest $request)
    {
        $message = 'Enregistrement d\'un projet';

        try {
            $result = $this->etapePrestationStatusRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Projet créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/etapePrestationStatuss/{id}",
     *      operationId="EtapePrestationStatus update",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="update one EtapePrestationStatus data ",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/EtapePrestationStatus")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatusCreate")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function update(UpdateEtapePrestationStatusRequest $request, $id)
    {
        $message = 'Mise à jour d\'un projet';

        try {
            $result = $this->etapePrestationStatusRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un projet effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/etapePrestationStatuss/{id}",
     *      operationId="EtapePrestationStatus Delete",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="delete EtapePrestationStatus data ",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/DeleteResponseData"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/DeleteResponseData")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function destroy($id)
    {
        $message = 'Suppression d\'un projet';

        try {
            $recup = $this->etapePrestationStatusRepository->get($id);

            $result = $this->etapePrestationStatusRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Projet supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/etapePrestationStatuss-search",
     *      operationId="EtapePrestationStatus searching",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Return list of EtapePrestationStatus respecting term",
     *      description="Get all filtered etapePrestationStatuss using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/EtapePrestationStatus")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/TermSearch")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function search(Request $request)
    {
        $message = 'Filtrage des projets';

        try {
            $term = $request->term;
            $result = $this->etapePrestationStatusRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etapePrestationStatuss/{id}/state/{state}",
     *      operationId="EtapePrestationStatus change state",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *  @OA\Parameter(
     *      name="state",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Apply new etapePrestationStatus for one EtapePrestationStatus ",
     *      description="Get  etapePrestationStatus by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapePrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/EtapePrestationStatus")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function changeState($id, $state)
    {
        $message = 'Changement de l\'état d\'un projet';

        try {
            $result = $this->etapePrestationStatusRepository->setEtapePrestationStatus($id, $state);
            $etapePrestationStatusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Projet $etapePrestationStatusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Add requests to a etapePrestationStatus
     * 
     * @OA\Post(
     *      path="/etapePrestationStatuss/{id}/add-requests",
     *      operationId="addRequestsToEtapePrestationStatus",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Add requests to a etapePrestationStatus",
     *      description="Add multiple request IDs to a specific etapePrestationStatus",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="EtapePrestationStatus ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\RequestBody(
     *         description="Request IDs array",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"request_ids"},
     *             @OA\Property(
     *                 property="request_ids",
     *                 type="array",
     *                 items=@OA\Items(type="integer"),
     *                 example={1, 2, 3, 4}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Requests added successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="EtapePrestationStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function addRequests(AddRequestsToEtapePrestationStatusRequest $request, $id)
    {
        $message = 'Ajout des requêtes à un projet';

        try {
            $etapePrestationStatus = $this->etapePrestationStatusRepository->addRequests($id, $request->request_ids);
            $this->ls->trace(['action_name' => $message, 'description' => 'Requêtes ajoutées au projet ' . $id]);

            return Common::success('Requêtes ajoutées au projet avec succès', $etapePrestationStatus);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Close a etapePrestationStatus and dispatch job to close all requests
     * 
     * @OA\Post(
     *      path="/etapePrestationStatuss/{id}/close",
     *      operationId="closeEtapePrestationStatus",
     *      tags={"EtapePrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Close a etapePrestationStatus",
     *      description="Close a etapePrestationStatus and dispatch a job to close all associated requests in background",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="EtapePrestationStatus ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="EtapePrestationStatus closure initiated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (etapePrestationStatus already closed)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="EtapePrestationStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function closeEtapePrestationStatus($id)
    {
        $message = 'Clôture d\'un projet';

        try {
            $etapePrestationStatus = $this->etapePrestationStatusRepository->get($id);

            if ($etapePrestationStatus->isClosed()) {
                return Common::error('Le projet est déjà clôturé', []);
            }

            CloseEtapePrestationStatusRequests::dispatch($id);

            $this->ls->trace(['action_name' => $message, 'description' => 'Clôture du projet ' . $id . ' initiée']);

            return Common::success(
                'Clôture du projet initiée. Les requêtes seront clôturées aussi',
                ['etapePrestationStatus_id' => $id, 'etapePrestationStatus' => 'closing']
            );
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}