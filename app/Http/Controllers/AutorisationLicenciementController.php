<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AutorisationLicenciementRepository;
use App\Http\Requests\AutorisationLicenciement\StoreAutorisationLicenciementRequest;
use App\Http\Requests\AutorisationLicenciement\UpdateAutorisationLicenciementRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AutorisationLicenciementController extends Controller
{
    /**
     * The autorisationLicenciement repository being queried.
     *
     * @var AutorisationLicenciementRepository
     */
    protected $autorisationLicenciementRepository;

    protected $ls;

    public function __construct(AutorisationLicenciementRepository $autorisationLicenciementRepository, LogService $ls)
    {
        $this->autorisationLicenciementRepository = $autorisationLicenciementRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/autorisationLicenciements",
     *      operationId="AutorisationLicenciement list",
     *      tags={"AutorisationLicenciement"},
     *       security={{"JWT":{}}},
     *      summary="Return autorisationLicenciement data",
     *      description="Get all autorisationLicenciements",
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
     *          description="AutorisationLicenciement Role ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciement"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationLicenciement")
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
        $message = 'Récupération de la liste des autorisationLicenciements';

        try {
            $result = $this->autorisationLicenciementRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    /** @OA\Get(
     *      path="/autorisationLicenciements/{id}",
     *      operationId="AutorisationLicenciement show",
     *      tags={"AutorisationLicenciement"},
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
     *          description="AutorisationLicenciement ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AutorisationLicenciement data",
     *      description="Get AutorisationLicenciement by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciement"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationLicenciement")
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
        $message = 'Récupération d\'un autorisationLicenciement';

        try {
            $result = $this->autorisationLicenciementRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/autorisationLicenciements",
     *      operationId="AutorisationLicenciement store",
     *      tags={"AutorisationLicenciement"},
     *       security={{"JWT":{}}},
     *      summary="Store AutorisationLicenciement data",
     *      description="Create a new AutorisationLicenciement",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciementCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciement"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationLicenciement")
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
    public function store(StoreAutorisationLicenciementRequest $request)
    {
        $message = 'Enregistrement d\'un autorisationLicenciement';

        try {
            $result = $this->autorisationLicenciementRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/autorisationLicenciements/{id}",
     *      operationId="AutorisationLicenciement update",
     *      tags={"AutorisationLicenciement"},
     *       security={{"JWT":{}}},
     *      summary="Update one AutorisationLicenciement data",
     *      description="Update AutorisationLicenciement by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationLicenciement ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciementCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciement"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationLicenciement")
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
    public function update(UpdateAutorisationLicenciementRequest $request, $id)
    {
        $message = 'Mise à jour d\'un autorisationLicenciement';

        try {
            $result = $this->autorisationLicenciementRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'autorisationLicenciement effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/autorisationLicenciements/{id}",
     *      operationId="AutorisationLicenciement Delete",
     *      tags={"AutorisationLicenciement"},
     *       security={{"JWT":{}}},
     *      summary="Delete AutorisationLicenciement data",
     *      description="Delete AutorisationLicenciement by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationLicenciement ID",
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
        $message = 'Suppression d\'un autorisationLicenciement';

        try {
            $recup = $this->autorisationLicenciementRepository->get($id);

            $result = $this->autorisationLicenciementRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/autorisationLicenciements/{id}/state/{state}",
     *      operationId="AutorisationLicenciement change state",
     *      tags={"AutorisationLicenciement"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AutorisationLicenciement ID",
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
     *          description="AutorisationLicenciement state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AutorisationLicenciement state",
     *      description="Change AutorisationLicenciement state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciement"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AutorisationLicenciement")
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
        $message = 'Changement de l\'état d\'un autorisationLicenciement';

        try {
            $result = $this->autorisationLicenciementRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/autorisationLicenciements-search",
     *      operationId="AutorisationLicenciement searching",
     *      tags={"AutorisationLicenciement"},
     *       security={{"JWT":{}}},
     *      summary="Return list of AutorisationLicenciement respecting term",
     *      description="Get all filtered autorisationLicenciements using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AutorisationLicenciement"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/AutorisationLicenciement")
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
        $message = 'Filtrage des autorisationLicenciements';

        try {
            $term = $request->term;
            $result = $this->autorisationLicenciementRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
