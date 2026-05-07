<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EtapeRepository;
use App\Http\Requests\Etape\StoreEtapeRequest;
use App\Http\Requests\Etape\UpdateEtapeRequest;
use App\Http\Requests\Etape\AddRequestsToEtapeRequest;
use App\Jobs\CloseEtapeRequests;
use App\Models\Etape;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EtapeController extends Controller
{
    /**
     * The etape being queried.
     *
     * @var Etape
     */
    protected $etapeRepository;

    protected $ls;

    public function __construct(EtapeRepository $etapeRepository, LogService $ls)
    {
        $this->etapeRepository = $etapeRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/etapes",
     *      operationId="Etape list",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="Return Etape data ",
     *      description="Get all etapes",
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
     *         @OA\JsonContent(ref="#/components/schemas/Etape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Etape")
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
            $result = $this->etapeRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etapes/{id}",
     *      operationId="Etape show",
     *      tags={"Etape"},
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
     *      summary="Return one Etape data with requests ",
     *      description="Get etape by ID with associated requests",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Etape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Etape")
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
            $result = $this->etapeRepository->getWithRequests($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Projet trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/etapes",
     *      operationId="Etape store",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="store Etape data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Etape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Etape")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapeCreate")
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
    public function store(StoreEtapeRequest $request)
    {
        $message = 'Enregistrement d\'un projet';

        try {
            $result = $this->etapeRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Projet créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/etapes/{id}",
     *      operationId="Etape update",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="update one Etape data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/Etape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Etape")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/EtapeCreate")
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
    public function update(UpdateEtapeRequest $request, $id)
    {
        $message = 'Mise à jour d\'un projet';

        try {
            $result = $this->etapeRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un projet effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/etapes/{id}",
     *      operationId="Etape Delete",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="delete Etape data ",
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
            $recup = $this->etapeRepository->get($id);

            $result = $this->etapeRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Projet supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/etapes-search",
     *      operationId="Etape searching",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="Return list of Etape respecting term",
     *      description="Get all filtered etapes using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Etape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Etape")
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
            $result = $this->etapeRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etapes/{id}/state/{state}",
     *      operationId="Etape change state",
     *      tags={"Etape"},
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
     *      summary="Apply new etape for one Etape ",
     *      description="Get  etape by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Etape"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Etape")
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
            $result = $this->etapeRepository->setEtape($id, $state);
            $etapeMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Projet $etapeMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Add requests to a etape
     * 
     * @OA\Post(
     *      path="/etapes/{id}/add-requests",
     *      operationId="addRequestsToEtape",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="Add requests to a etape",
     *      description="Add multiple request IDs to a specific etape",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Etape ID",
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
     *         description="Etape not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function addRequests(AddRequestsToEtapeRequest $request, $id)
    {
        $message = 'Ajout des requêtes à un projet';

        try {
            $etape = $this->etapeRepository->addRequests($id, $request->request_ids);
            $this->ls->trace(['action_name' => $message, 'description' => 'Requêtes ajoutées au projet ' . $id]);

            return Common::success('Requêtes ajoutées au projet avec succès', $etape);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Close a etape and dispatch job to close all requests
     * 
     * @OA\Post(
     *      path="/etapes/{id}/close",
     *      operationId="closeEtape",
     *      tags={"Etape"},
     *      security={{"JWT":{}}},
     *      summary="Close a etape",
     *      description="Close a etape and dispatch a job to close all associated requests in background",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Etape ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Etape closure initiated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (etape already closed)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Etape not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function closeEtape($id)
    {
        $message = 'Clôture d\'un projet';

        try {
            $etape = $this->etapeRepository->get($id);

            if ($etape->isClosed()) {
                return Common::error('Le projet est déjà clôturé', []);
            }

            CloseEtapeRequests::dispatch($id);

            $this->ls->trace(['action_name' => $message, 'description' => 'Clôture du projet ' . $id . ' initiée']);

            return Common::success(
                'Clôture du projet initiée. Les requêtes seront clôturées aussi',
                ['etape_id' => $id, 'etape' => 'closing']
            );
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}