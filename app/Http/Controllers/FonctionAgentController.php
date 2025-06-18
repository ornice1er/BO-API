<?php

namespace App\Http\Controllers;

use App\Http\Repositories\FonctionAgentRepository;
use App\Http\Requests\FonctionAgent\StoreFonctionAgentRequest;
use App\Http\Requests\FonctionAgent\UpdateFonctionAgentRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FonctionAgentController extends Controller
{
    /**
     * The fonctionAgent repository being queried.
     *
     * @var FonctionAgentRepository
     */
    protected $fonctionAgentRepository;

    protected $ls;

    public function __construct(FonctionAgentRepository $fonctionAgentRepository, LogService $ls)
    {
        $this->fonctionAgentRepository = $fonctionAgentRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/fonctionAgents",
     *      operationId="FonctionAgent list",
     *      tags={"FonctionAgent"},
     *       security={{"JWT":{}}},
     *      summary="Return fonctionAgent data",
     *      description="Get all fonctionAgents",
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
     *          description="FonctionAgent Role ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/FonctionAgent")
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
        $message = 'Récupération de la liste des fonctionAgents';

        try {
            $result = $this->fonctionAgentRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    /** @OA\Get(
     *      path="/fonctionAgents/{id}",
     *      operationId="FonctionAgent show",
     *      tags={"FonctionAgent"},
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
     *          description="FonctionAgent ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one FonctionAgent data",
     *      description="Get FonctionAgent by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/FonctionAgent")
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
        $message = 'Récupération d\'un fonctionAgent';

        try {
            $result = $this->fonctionAgentRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/fonctionAgents",
     *      operationId="FonctionAgent store",
     *      tags={"FonctionAgent"},
     *       security={{"JWT":{}}},
     *      summary="Store FonctionAgent data",
     *      description="Create a new FonctionAgent",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgentCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/FonctionAgent")
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
    public function store(StoreFonctionAgentRequest $request)
    {
        $message = 'Enregistrement d\'un fonctionAgent';

        try {
            $result = $this->fonctionAgentRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/fonctionAgents/{id}",
     *      operationId="FonctionAgent update",
     *      tags={"FonctionAgent"},
     *       security={{"JWT":{}}},
     *      summary="Update one FonctionAgent data",
     *      description="Update FonctionAgent by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="FonctionAgent ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgentCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/FonctionAgent")
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
    public function update(UpdateFonctionAgentRequest $request, $id)
    {
        $message = 'Mise à jour d\'un fonctionAgent';

        try {
            $result = $this->fonctionAgentRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'fonctionAgent effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/fonctionAgents/{id}",
     *      operationId="FonctionAgent Delete",
     *      tags={"FonctionAgent"},
     *       security={{"JWT":{}}},
     *      summary="Delete FonctionAgent data",
     *      description="Delete FonctionAgent by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="FonctionAgent ID",
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
        $message = 'Suppression d\'un fonctionAgent';

        try {
            $recup = $this->fonctionAgentRepository->get($id);

            $result = $this->fonctionAgentRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/fonctionAgents/{id}/state/{state}",
     *      operationId="FonctionAgent change state",
     *      tags={"FonctionAgent"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="FonctionAgent ID",
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
     *          description="FonctionAgent state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change FonctionAgent state",
     *      description="Change FonctionAgent state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/FonctionAgent"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/FonctionAgent")
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
        $message = 'Changement de l\'état d\'un fonctionAgent';

        try {
            $result = $this->fonctionAgentRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/fonctionAgents-search",
     *      operationId="FonctionAgent searching",
     *      tags={"FonctionAgent"},
     *       security={{"JWT":{}}},
     *      summary="Return list of FonctionAgent respecting term",
     *      description="Get all filtered fonctionAgents using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FonctionAgent"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/FonctionAgent")
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
        $message = 'Filtrage des fonctionAgents';

        try {
            $term = $request->term;
            $result = $this->fonctionAgentRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
