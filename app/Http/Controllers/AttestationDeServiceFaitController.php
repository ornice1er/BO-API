<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AttestationDeServiceFaitRepository;
use App\Http\Requests\AttestationDeServiceFait\StoreAttestationDeServiceFaitRequest;
use App\Http\Requests\AttestationDeServiceFait\UpdateAttestationDeServiceFaitRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AttestationDeServiceFaitController extends Controller
{
    /**
     * The attestationDeServiceFait repository being queried.
     *
     * @var AttestationDeServiceFaitRepository
     */
    protected $attestationDeServiceFaitRepository;

    protected $ls;

    public function __construct(AttestationDeServiceFaitRepository $attestationDeServiceFaitRepository, LogService $ls)
    {
        $this->attestationDeServiceFaitRepository = $attestationDeServiceFaitRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/attestationDeServiceFaits",
     *      operationId="AttestationDeServiceFait list",
     *      tags={"AttestationDeServiceFait"},
     *       security={{"JWT":{}}},
     *      summary="Return attestationDeServiceFait data",
     *      description="Get all attestationDeServiceFaits",
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
     *          description="AttestationDeServiceFait Role ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFait"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDeServiceFait")
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
        $message = 'Récupération de la liste des attestationDeServiceFaits';

        try {
            $result = $this->attestationDeServiceFaitRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    /** @OA\Get(
     *      path="/attestationDeServiceFaits/{id}",
     *      operationId="AttestationDeServiceFait show",
     *      tags={"AttestationDeServiceFait"},
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
     *          description="AttestationDeServiceFait ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one AttestationDeServiceFait data",
     *      description="Get AttestationDeServiceFait by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFait"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDeServiceFait")
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
        $message = 'Récupération d\'un attestationDeServiceFait';

        try {
            $result = $this->attestationDeServiceFaitRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/attestationDeServiceFaits",
     *      operationId="AttestationDeServiceFait store",
     *      tags={"AttestationDeServiceFait"},
     *       security={{"JWT":{}}},
     *      summary="Store AttestationDeServiceFait data",
     *      description="Create a new AttestationDeServiceFait",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFaitCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFait"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDeServiceFait")
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
    public function store(StoreAttestationDeServiceFaitRequest $request)
    {
        $message = 'Enregistrement d\'un attestationDeServiceFait';

        try {
            $result = $this->attestationDeServiceFaitRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/attestationDeServiceFaits/{id}",
     *      operationId="AttestationDeServiceFait update",
     *      tags={"AttestationDeServiceFait"},
     *       security={{"JWT":{}}},
     *      summary="Update one AttestationDeServiceFait data",
     *      description="Update AttestationDeServiceFait by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AttestationDeServiceFait ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFaitCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFait"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDeServiceFait")
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
    public function update(UpdateAttestationDeServiceFaitRequest $request, $id)
    {
        $message = 'Mise à jour d\'un attestationDeServiceFait';

        try {
            $result = $this->attestationDeServiceFaitRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'attestationDeServiceFait effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/attestationDeServiceFaits/{id}",
     *      operationId="AttestationDeServiceFait Delete",
     *      tags={"AttestationDeServiceFait"},
     *       security={{"JWT":{}}},
     *      summary="Delete AttestationDeServiceFait data",
     *      description="Delete AttestationDeServiceFait by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AttestationDeServiceFait ID",
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
        $message = 'Suppression d\'un attestationDeServiceFait';

        try {
            $recup = $this->attestationDeServiceFaitRepository->get($id);

            $result = $this->attestationDeServiceFaitRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/attestationDeServiceFaits/{id}/state/{state}",
     *      operationId="AttestationDeServiceFait change state",
     *      tags={"AttestationDeServiceFait"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="AttestationDeServiceFait ID",
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
     *          description="AttestationDeServiceFait state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change AttestationDeServiceFait state",
     *      description="Change AttestationDeServiceFait state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFait"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/AttestationDeServiceFait")
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
        $message = 'Changement de l\'état d\'un attestationDeServiceFait';

        try {
            $result = $this->attestationDeServiceFaitRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/attestationDeServiceFaits-search",
     *      operationId="AttestationDeServiceFait searching",
     *      tags={"AttestationDeServiceFait"},
     *       security={{"JWT":{}}},
     *      summary="Return list of AttestationDeServiceFait respecting term",
     *      description="Get all filtered attestationDeServiceFaits using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AttestationDeServiceFait"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/AttestationDeServiceFait")
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
        $message = 'Filtrage des attestationDeServiceFaits';

        try {
            $term = $request->term;
            $result = $this->attestationDeServiceFaitRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
