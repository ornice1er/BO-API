<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AutorisationDeStageRepository;
use App\Http\Requests\AutorisationDeStage\StoreAutorisationDeStageRequest;
use App\Http\Requests\AutorisationDeStage\UpdateAutorisationDeStageRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AutorisationDeStageController extends Controller
{
    /**
     * The autorisationDeStage repository being queried.
     *
     * @var AutorisationDeStageRepository
     */
    protected $autorisationDeStageRepository;

    protected $ls;

    public function __construct(AutorisationDeStageRepository $autorisationDeStageRepository, LogService $ls)
    {
        $this->autorisationDeStageRepository = $autorisationDeStageRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/autorisationDeStages",
     *      operationId="AutorisationDeStage list",
     *      tags={"AutorisationDeStage"},
     *       security={{"JWT":{}}},
     *      summary="Return autorisationDeStage data",
     *      description="Get all autorisationDeStages",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Can be used for filtering data by name",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     * @OA\Parameter(
     *          name="project_id",
     *          in="query",
     *          description="Project ID",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     * @OA\Parameter(
     *          name="role",
     *          in="query",
     *          description="AutorisationDeStage Role ID",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="categorie",
     *          in="query",
     *          description="Can be used for filtering data by categorie| ANIMATRICE,RESPONSABLE",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *          ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStage"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationDeStage")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function index(Request $request)
    {
        $message = 'Récupération de la liste des autorisationDeStages';

        try {
            $result = $this->autorisationDeStageRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    /** @OA\Get(
     *      path="/autorisationDeStages/{id}",
     *      operationId="AutorisationDeStage show",
     *      tags={"AutorisationDeStage"},
     *       security={{"JWT":{}}},
     *
     *  @OA\Parameter(
     *          name="project_id",
     *          in="query",
     *          description="Project ID",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationDeStage ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AutorisationDeStage data",
     *      description="Get AutorisationDeStage by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStage"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationDeStage")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function show(Request $request, $id)
    {
        $message = 'Récupération d\'un autorisationDeStage';

        try {
            $result = $this->autorisationDeStageRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/autorisationDeStages",
     *      operationId="AutorisationDeStage store",
     *      tags={"AutorisationDeStage"},
     *       security={{"JWT":{}}},
     *      summary="Store AutorisationDeStage data",
     *      description="Create a new AutorisationDeStage",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStageCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStage"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationDeStage")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function store(StoreAutorisationDeStageRequest $request)
    {
        $message = 'Enregistrement d\'un autorisationDeStage';

        try {
            $result = $this->autorisationDeStageRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/autorisationDeStages/{id}",
     *      operationId="AutorisationDeStage update",
     *      tags={"AutorisationDeStage"},
     *       security={{"JWT":{}}},
     *      summary="Update one AutorisationDeStage data",
     *      description="Update AutorisationDeStage by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationDeStage ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStageCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStage"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationDeStage")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function update(UpdateAutorisationDeStageRequest $request, $id)
    {
        $message = 'Mise à jour d\'un autorisationDeStage';

        try {
            $result = $this->autorisationDeStageRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'autorisationDeStage effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/autorisationDeStages/{id}",
     *      operationId="AutorisationDeStage Delete",
     *      tags={"AutorisationDeStage"},
     *       security={{"JWT":{}}},
     *      summary="Delete AutorisationDeStage data",
     *      description="Delete AutorisationDeStage by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationDeStage ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DeleteResponseData"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/DeleteResponseData")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function destroy($id)
    {
        $message = 'Suppression d\'un autorisationDeStage';

        try {
            $recup = $this->autorisationDeStageRepository->get($id);

            $result = $this->autorisationDeStageRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/autorisationDeStages/{id}/state/{state}",
     *      operationId="AutorisationDeStage change state",
     *      tags={"AutorisationDeStage"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationDeStage ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="state",
     *          in="path",
     *          description="AutorisationDeStage state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AutorisationDeStage state",
     *      description="Change AutorisationDeStage state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationDeStage"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationDeStage")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function changeState($id, $state)
    {
        $message = 'Changement de l\'état d\'un autorisationDeStage';

        try {
            $result = $this->autorisationDeStageRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/autorisationDeStages-search",
     *      operationId="AutorisationDeStage searching",
     *      tags={"AutorisationDeStage"},
     *       security={{"JWT":{}}},
     *      summary="Return list of AutorisationDeStage respecting term",
     *      description="Get all filtered autorisationDeStages using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AutorisationDeStage"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/AutorisationDeStage")
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
        $message = 'Filtrage des autorisationDeStages';

        try {
            $term = $request->term;
            $result = $this->autorisationDeStageRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
