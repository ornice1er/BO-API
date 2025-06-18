<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AdherantMemberCmpsRepository;
use App\Http\Requests\AdherantMemberCmps\StoreCandidateAdherantMemberCmpsRequest;
use App\Http\Requests\AdherantMemberCmps\StorePRAdherantMemberCmpsRequest;
use App\Http\Requests\AdherantMemberCmps\StoreAdherantMemberCmpsRequest;
use App\Http\Requests\AdherantMemberCmps\UpdateAdherantMemberCmpsRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdherantMemberCmpsController extends Controller
{
    /**
     * The adherantMemberCmps repository being queried.
     *
     * @var AdherantMemberCmpsRepository
     */
    protected $adherantMemberCmpsRepository;

    protected $ls;

    public function __construct(AdherantMemberCmpsRepository $adherantMemberCmpsRepository, LogService $ls)
    {
        $this->adherantMemberCmpsRepository = $adherantMemberCmpsRepository;
        $this->ls = $ls;

        $this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/adherantMemberCmpss",
     *      operationId="AdherantMemberCmps list",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Return adherantMemberCmps data",
     *      description="Get all adherantMemberCmpss",
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
     *          description="AdherantMemberCmps Role ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
        $message = 'Récupération de la liste des utilisateurs';

        try {
            $result = $this->adherantMemberCmpsRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/adherantMemberCmpss-notified",
     *      operationId="AdherantMemberCmps notified list",
     *      tags={"AdherantMemberCmps"},
     *      summary="Return adherantMemberCmps data",
     *      description="Get all adherantMemberCmpss",
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
     *          description="AdherantMemberCmps Role ID",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
    public function getNotified(Request $request)
    {

        $authorizationHeader = request()->header('Authorization');

        if ($request->header('Authorization') == null) {
            return Common::error('authorization header not found', []);
        }

        $encodedCredentials = substr($authorizationHeader, 6);

        // Décoder les identifiants
        $decodedCredentials = base64_decode($encodedCredentials);

        // Séparer l'adherantMemberCmpsname et le password
        [$adherantMemberCmpsname, $password] = explode(':', $decodedCredentials, 2);
        $envAdherantMemberCmpsname = env('BASIC_AUTH_USERNAME');
        $envPassword = env('BASIC_AUTH_PASSWORD');

        if ($adherantMemberCmpsname !== $envAdherantMemberCmpsname || $password !== $envPassword) {
            return Common::error('Accès non autorisé', []);
        }

        $message = 'Récupération de la liste des utilisateurs';

        try {
            $result = $this->adherantMemberCmpsRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result->select('id'));
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/adherantMemberCmpss-rh",
     *      operationId="AdherantMemberCmps RH list",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Return adherantMemberCmps data",
     *      description="Get all adherantMemberCmpss",
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
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
    public function indexRH(Request $request)
    {
        $message = 'Récupération de la liste des utilisateurs pour RH';

        try {
            $result = $this->adherantMemberCmpsRepository->getAllRH($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/adherantMemberCmpss/{id}",
     *      operationId="AdherantMemberCmps show",
     *      tags={"AdherantMemberCmps"},
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
     *          description="AdherantMemberCmps ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AdherantMemberCmps data",
     *      description="Get AdherantMemberCmps by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
        $message = 'Récupération d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/adherantMemberCmpss-rh/{id}",
     *      operationId="AdherantMemberCmps RH show",
     *      tags={"AdherantMemberCmps"},
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
     *          description="AdherantMemberCmps ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AdherantMemberCmps data",
     *      description="Get AdherantMemberCmps by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
    public function showRH($id)
    {
        $message = 'Récupération d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->getRH($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/adherantMemberCmpss",
     *      operationId="AdherantMemberCmps store",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Store AdherantMemberCmps data",
     *      description="Create a new AdherantMemberCmps",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmpsCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
    public function store(StoreAdherantMemberCmpsRequest $request)
    {
        $message = 'Enregistrement d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/adherantMemberCmpss-candidate",
     *      operationId="Create AdherantMemberCmps",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Store AdherantMemberCmps data",
     *      description="Create a new AdherantMemberCmps",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmpsCreate")
     *
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
    public function createAdherantMemberCmps(StoreCandidateAdherantMemberCmpsRequest $request)
    {
        $message = 'Enregistrement d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->makeStore2($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function createAdherantMemberCmpsPR(StorePRAdherantMemberCmpsRequest $request)
    {
        $message = 'Enregistrement d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->makeStorePR($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/adherantMemberCmpss/{id}",
     *      operationId="AdherantMemberCmps update",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Update one AdherantMemberCmps data",
     *      description="Update AdherantMemberCmps by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AdherantMemberCmps ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmpsCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
    public function update(UpdateAdherantMemberCmpsRequest $request, $id)
    {
        $message = 'Mise à jour d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'utilisateur effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/adherantMemberCmpss/{id}",
     *      operationId="AdherantMemberCmps Delete",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Delete AdherantMemberCmps data",
     *      description="Delete AdherantMemberCmps by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AdherantMemberCmps ID",
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
        $message = 'Suppression d\'un utilisateur';

        try {
            $recup = $this->adherantMemberCmpsRepository->get($id);

            $result = $this->adherantMemberCmpsRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/adherantMemberCmpss/{id}/state/{state}",
     *      operationId="AdherantMemberCmps change state",
     *      tags={"AdherantMemberCmps"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AdherantMemberCmps ID",
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
     *          description="AdherantMemberCmps state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AdherantMemberCmps state",
     *      description="Change AdherantMemberCmps state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
        $message = 'Changement de l\'état d\'un utilisateur';

        try {
            $result = $this->adherantMemberCmpsRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/adherantMemberCmpss-search",
     *      operationId="AdherantMemberCmps searching",
     *      tags={"AdherantMemberCmps"},
     *       security={{"JWT":{}}},
     *      summary="Return list of AdherantMemberCmps respecting term",
     *      description="Get all filtered adherantMemberCmpss using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdherantMemberCmps"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/AdherantMemberCmps")
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
        $message = 'Filtrage des utilisateurs';

        try {
            $term = $request->term;
            $result = $this->adherantMemberCmpsRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
