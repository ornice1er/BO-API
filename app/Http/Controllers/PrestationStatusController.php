<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PrestationStatusRepository;
use App\Http\Requests\PrestationStatus\StorePrestationStatusRequest;
use App\Http\Requests\PrestationStatus\UpdatePrestationStatusRequest;
use App\Http\Requests\PrestationStatus\AddRequestsToPrestationStatusRequest;
use App\Jobs\ClosePrestationStatusRequests;
use App\Models\PrestationStatus;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PrestationStatusController extends Controller
{
    /**
     * The prestationStatus being queried.
     *
     * @var PrestationStatus
     */
    protected $prestationStatusRepository;

    protected $ls;

    public function __construct(PrestationStatusRepository $prestationStatusRepository, LogService $ls)
    {
        $this->prestationStatusRepository = $prestationStatusRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/prestationStatuss",
     *      operationId="PrestationStatus list",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Return PrestationStatus data ",
     *      description="Get all prestationStatuss",
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
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/PrestationStatus")
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
            $result = $this->prestationStatusRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/prestationStatuss/{id}",
     *      operationId="PrestationStatus show",
     *      tags={"PrestationStatus"},
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
     *      summary="Return one PrestationStatus data with requests ",
     *      description="Get prestationStatus by ID with associated requests",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/PrestationStatus")
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
            $result = $this->prestationStatusRepository->getWithRequests($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Projet trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/prestationStatuss",
     *      operationId="PrestationStatus store",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="store PrestationStatus data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/PrestationStatus")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatusCreate")
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
    public function store(StorePrestationStatusRequest $request)
    {
        $message = 'Enregistrement d\'un projet';

        try {
            $result = $this->prestationStatusRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Projet créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/prestationStatuss/{id}",
     *      operationId="PrestationStatus update",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="update one PrestationStatus data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/PrestationStatus")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatusCreate")
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
    public function update(UpdatePrestationStatusRequest $request, $id)
    {
        $message = 'Mise à jour d\'un projet';

        try {
            $result = $this->prestationStatusRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un projet effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/prestationStatuss/{id}",
     *      operationId="PrestationStatus Delete",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="delete PrestationStatus data ",
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
            $recup = $this->prestationStatusRepository->get($id);

            $result = $this->prestationStatusRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Projet supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/prestationStatuss-search",
     *      operationId="PrestationStatus searching",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Return list of PrestationStatus respecting term",
     *      description="Get all filtered prestationStatuss using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/PrestationStatus")
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
            $term = $request->input('search') ?? $request->input('term');
            $result = $this->prestationStatusRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/prestationStatuss/{id}/state/{state}",
     *      operationId="PrestationStatus change state",
     *      tags={"PrestationStatus"},
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
     *      summary="Apply new status for one PrestationStatus ",
     *      description="Get  prestationStatus by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PrestationStatus"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/PrestationStatus")
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
            $result = $this->prestationStatusRepository->setPrestationStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Projet $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Add requests to a prestationStatus
     * 
     * @OA\Post(
     *      path="/prestationStatuss/{id}/add-requests",
     *      operationId="addRequestsToPrestationStatus",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Add requests to a prestationStatus",
     *      description="Add multiple request IDs to a specific prestationStatus",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="PrestationStatus ID",
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
     *         description="PrestationStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function addRequests(AddRequestsToPrestationStatusRequest $request, $id)
    {
        $message = 'Ajout des requêtes à un projet';

        try {
            $prestationStatus = $this->prestationStatusRepository->addRequests($id, $request->request_ids);
            $this->ls->trace(['action_name' => $message, 'description' => 'Requêtes ajoutées au projet ' . $id]);

            return Common::success('Requêtes ajoutées au projet avec succès', $prestationStatus);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Close a prestationStatus and dispatch job to close all requests
     * 
     * @OA\Post(
     *      path="/prestationStatuss/{id}/close",
     *      operationId="closePrestationStatus",
     *      tags={"PrestationStatus"},
     *      security={{"JWT":{}}},
     *      summary="Close a prestationStatus",
     *      description="Close a prestationStatus and dispatch a job to close all associated requests in background",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="PrestationStatus ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PrestationStatus closure initiated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (prestationStatus already closed)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="PrestationStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function closePrestationStatus($id)
    {
        $message = 'Clôture d\'un projet';

        try {
            $prestationStatus = $this->prestationStatusRepository->get($id);

            if ($prestationStatus->isClosed()) {
                return Common::error('Le projet est déjà clôturé', []);
            }

            ClosePrestationStatusRequests::dispatch($id);

            $this->ls->trace(['action_name' => $message, 'description' => 'Clôture du projet ' . $id . ' initiée']);

            return Common::success(
                'Clôture du projet initiée. Les requêtes seront clôturées aussi',
                ['prestationStatus_id' => $id, 'status' => 'closing']
            );
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}