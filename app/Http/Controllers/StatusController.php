<?php

namespace App\Http\Controllers;

use App\Http\Repositories\StatusRepository;
use App\Http\Requests\Status\StoreStatusRequest;
use App\Http\Requests\Status\UpdateStatusRequest;
use App\Http\Requests\Status\AddRequestsToStatusRequest;
use App\Jobs\CloseStatusRequests;
use App\Models\Status;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class StatusController extends Controller
{
    /**
     * The status being queried.
     *
     * @var Status
     */
    protected $statusRepository;

    protected $ls;

    public function __construct(StatusRepository $statusRepository, LogService $ls)
    {
        $this->statusRepository = $statusRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/statuss",
     *      operationId="Status list",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="Return Status data ",
     *      description="Get all statuss",
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
     *         @OA\JsonContent(ref="#/components/schemas/Status"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Status")
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
            $result = $this->statusRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/statuss/{id}",
     *      operationId="Status show",
     *      tags={"Status"},
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
     *      summary="Return one Status data with requests ",
     *      description="Get status by ID with associated requests",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Status"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Status")
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
            $result = $this->statusRepository->getWithRequests($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Projet trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/statuss",
     *      operationId="Status store",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="store Status data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Status"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Status")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StatusCreate")
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
    public function store(StoreStatusRequest $request)
    {
        $message = 'Enregistrement d\'un projet';

        try {
            $result = $this->statusRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Projet créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/statuss/{id}",
     *      operationId="Status update",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="update one Status data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/Status"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Status")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StatusCreate")
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
    public function update(UpdateStatusRequest $request, $id)
    {
        $message = 'Mise à jour d\'un projet';

        try {
            $result = $this->statusRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un projet effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/statuss/{id}",
     *      operationId="Status Delete",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="delete Status data ",
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
            $recup = $this->statusRepository->get($id);

            $result = $this->statusRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Projet supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/statuss-search",
     *      operationId="Status searching",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="Return list of Status respecting term",
     *      description="Get all filtered statuss using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Status"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Status")
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
            $result = $this->statusRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/statuss/{id}/state/{state}",
     *      operationId="Status change state",
     *      tags={"Status"},
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
     *      summary="Apply new status for one Status ",
     *      description="Get  status by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Status"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Status")
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
            $result = $this->statusRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Projet $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Add requests to a status
     * 
     * @OA\Post(
     *      path="/statuss/{id}/add-requests",
     *      operationId="addRequestsToStatus",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="Add requests to a status",
     *      description="Add multiple request IDs to a specific status",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Status ID",
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
     *         description="Status not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function addRequests(AddRequestsToStatusRequest $request, $id)
    {
        $message = 'Ajout des requêtes à un projet';

        try {
            $status = $this->statusRepository->addRequests($id, $request->request_ids);
            $this->ls->trace(['action_name' => $message, 'description' => 'Requêtes ajoutées au projet ' . $id]);

            return Common::success('Requêtes ajoutées au projet avec succès', $status);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Close a status and dispatch job to close all requests
     * 
     * @OA\Post(
     *      path="/statuss/{id}/close",
     *      operationId="closeStatus",
     *      tags={"Status"},
     *      security={{"JWT":{}}},
     *      summary="Close a status",
     *      description="Close a status and dispatch a job to close all associated requests in background",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Status ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status closure initiated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (status already closed)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Status not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function closeStatus($id)
    {
        $message = 'Clôture d\'un projet';

        try {
            $status = $this->statusRepository->get($id);

            if ($status->isClosed()) {
                return Common::error('Le projet est déjà clôturé', []);
            }

            CloseStatusRequests::dispatch($id);

            $this->ls->trace(['action_name' => $message, 'description' => 'Clôture du projet ' . $id . ' initiée']);

            return Common::success(
                'Clôture du projet initiée. Les requêtes seront clôturées aussi',
                ['status_id' => $id, 'status' => 'closing']
            );
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}