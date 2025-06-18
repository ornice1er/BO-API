<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AttestationDePresenceAuPosteRepository;
use App\Http\Requests\AttestationDePresenceAuPoste\StoreAttestationDePresenceAuPosteRequest;
use App\Http\Requests\AttestationDePresenceAuPoste\UpdateAttestationDePresenceAuPosteRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AttestationDePresenceAuPosteController extends Controller
{
    /**
     * The attestationDePresenceAuPoste repository being queried.
     *
     * @var AttestationDePresenceAuPosteRepository
     */
    protected $attestationDePresenceAuPosteRepository;

    protected $ls;

    public function __construct(AttestationDePresenceAuPosteRepository $attestationDePresenceAuPosteRepository, LogService $ls)
    {
        $this->attestationDePresenceAuPosteRepository = $attestationDePresenceAuPosteRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/attestationDePresenceAuPostes",
     *      operationId="AttestationDePresenceAuPoste list",
     *      tags={"AttestationDePresenceAuPoste"},
     *       security={{"JWT":{}}},
     *      summary="Return attestationDePresenceAuPoste data",
     *      description="Get all attestationDePresenceAuPostes",
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
     *          description="AttestationDePresenceAuPoste Role ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPoste"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDePresenceAuPoste")
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
        $message = 'Récupération de la liste des attestationDePresenceAuPostes';

        try {
            $result = $this->attestationDePresenceAuPosteRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    /** @OA\Get(
     *      path="/attestationDePresenceAuPostes/{id}",
     *      operationId="AttestationDePresenceAuPoste show",
     *      tags={"AttestationDePresenceAuPoste"},
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
     *          description="AttestationDePresenceAuPoste ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AttestationDePresenceAuPoste data",
     *      description="Get AttestationDePresenceAuPoste by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPoste"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDePresenceAuPoste")
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
        $message = 'Récupération d\'un attestationDePresenceAuPoste';

        try {
            $result = $this->attestationDePresenceAuPosteRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/attestationDePresenceAuPostes",
     *      operationId="AttestationDePresenceAuPoste store",
     *      tags={"AttestationDePresenceAuPoste"},
     *       security={{"JWT":{}}},
     *      summary="Store AttestationDePresenceAuPoste data",
     *      description="Create a new AttestationDePresenceAuPoste",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPosteCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPoste"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDePresenceAuPoste")
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
    public function store(StoreAttestationDePresenceAuPosteRequest $request)
    {
        $message = 'Enregistrement d\'un attestationDePresenceAuPoste';

        try {
            $result = $this->attestationDePresenceAuPosteRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/attestationDePresenceAuPostes/{id}",
     *      operationId="AttestationDePresenceAuPoste update",
     *      tags={"AttestationDePresenceAuPoste"},
     *       security={{"JWT":{}}},
     *      summary="Update one AttestationDePresenceAuPoste data",
     *      description="Update AttestationDePresenceAuPoste by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AttestationDePresenceAuPoste ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPosteCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPoste"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDePresenceAuPoste")
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
    public function update(UpdateAttestationDePresenceAuPosteRequest $request, $id)
    {
        $message = 'Mise à jour d\'un attestationDePresenceAuPoste';

        try {
            $result = $this->attestationDePresenceAuPosteRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'attestationDePresenceAuPoste effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/attestationDePresenceAuPostes/{id}",
     *      operationId="AttestationDePresenceAuPoste Delete",
     *      tags={"AttestationDePresenceAuPoste"},
     *       security={{"JWT":{}}},
     *      summary="Delete AttestationDePresenceAuPoste data",
     *      description="Delete AttestationDePresenceAuPoste by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AttestationDePresenceAuPoste ID",
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
        $message = 'Suppression d\'un attestationDePresenceAuPoste';

        try {
            $recup = $this->attestationDePresenceAuPosteRepository->get($id);

            $result = $this->attestationDePresenceAuPosteRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/attestationDePresenceAuPostes/{id}/state/{state}",
     *      operationId="AttestationDePresenceAuPoste change state",
     *      tags={"AttestationDePresenceAuPoste"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AttestationDePresenceAuPoste ID",
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
     *          description="AttestationDePresenceAuPoste state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AttestationDePresenceAuPoste state",
     *      description="Change AttestationDePresenceAuPoste state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPoste"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDePresenceAuPoste")
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
        $message = 'Changement de l\'état d\'un attestationDePresenceAuPoste';

        try {
            $result = $this->attestationDePresenceAuPosteRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/attestationDePresenceAuPostes-search",
     *      operationId="AttestationDePresenceAuPoste searching",
     *      tags={"AttestationDePresenceAuPoste"},
     *       security={{"JWT":{}}},
     *      summary="Return list of AttestationDePresenceAuPoste respecting term",
     *      description="Get all filtered attestationDePresenceAuPostes using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AttestationDePresenceAuPoste"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/AttestationDePresenceAuPoste")
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
        $message = 'Filtrage des attestationDePresenceAuPostes';

        try {
            $term = $request->term;
            $result = $this->attestationDePresenceAuPosteRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
