<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CertificatDeNonRadiationRepository;
use App\Http\Requests\CertificatDeNonRadiation\StoreCertificatDeNonRadiationRequest;
use App\Http\Requests\CertificatDeNonRadiation\UpdateCertificatDeNonRadiationRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CertificatDeNonRadiationController extends Controller
{
    /**
     * The certificatDeNonRadiation repository being queried.
     *
     * @var CertificatDeNonRadiationRepository
     */
    protected $certificatDeNonRadiationRepository;

    protected $ls;

    public function __construct(CertificatDeNonRadiationRepository $certificatDeNonRadiationRepository, LogService $ls)
    {
        $this->certificatDeNonRadiationRepository = $certificatDeNonRadiationRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    /** @OA\Get(
     *      path="/certificatDeNonRadiations",
     *      operationId="CertificatDeNonRadiation list",
     *      tags={"CertificatDeNonRadiation"},
     *       security={{"JWT":{}}},
     *      summary="Return certificatDeNonRadiation data",
     *      description="Get all certificatDeNonRadiations",
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
     *          description="CertificatDeNonRadiation Role ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiation"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/CertificatDeNonRadiation")
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
        $message = 'Récupération de la liste des certificatDeNonRadiations';

        try {
            $result = $this->certificatDeNonRadiationRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    /** @OA\Get(
     *      path="/certificatDeNonRadiations/{id}",
     *      operationId="CertificatDeNonRadiation show",
     *      tags={"CertificatDeNonRadiation"},
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
     *          description="CertificatDeNonRadiation ID",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one CertificatDeNonRadiation data",
     *      description="Get CertificatDeNonRadiation by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiation"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/CertificatDeNonRadiation")
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
        $message = 'Récupération d\'un certificatDeNonRadiation';

        try {
            $result = $this->certificatDeNonRadiationRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Utilisateur trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/certificatDeNonRadiations",
     *      operationId="CertificatDeNonRadiation store",
     *      tags={"CertificatDeNonRadiation"},
     *       security={{"JWT":{}}},
     *      summary="Store CertificatDeNonRadiation data",
     *      description="Create a new CertificatDeNonRadiation",
     *
     *       @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiationCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiation"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/CertificatDeNonRadiation")
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
    public function store(StoreCertificatDeNonRadiationRequest $request)
    {
        $message = 'Enregistrement d\'un certificatDeNonRadiation';

        try {
            $result = $this->certificatDeNonRadiationRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Utilisateur créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Put(
     *      path="/certificatDeNonRadiations/{id}",
     *      operationId="CertificatDeNonRadiation update",
     *      tags={"CertificatDeNonRadiation"},
     *       security={{"JWT":{}}},
     *      summary="Update one CertificatDeNonRadiation data",
     *      description="Update CertificatDeNonRadiation by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="CertificatDeNonRadiation ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiationCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiation"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/CertificatDeNonRadiation")
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
    public function update(UpdateCertificatDeNonRadiationRequest $request, $id)
    {
        $message = 'Mise à jour d\'un certificatDeNonRadiation';

        try {
            $result = $this->certificatDeNonRadiationRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour de l\'certificatDeNonRadiation effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/certificatDeNonRadiations/{id}",
     *      operationId="CertificatDeNonRadiation Delete",
     *      tags={"CertificatDeNonRadiation"},
     *       security={{"JWT":{}}},
     *      summary="Delete CertificatDeNonRadiation data",
     *      description="Delete CertificatDeNonRadiation by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="CertificatDeNonRadiation ID",
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
        $message = 'Suppression d\'un certificatDeNonRadiation';

        try {
            $recup = $this->certificatDeNonRadiationRepository->get($id);

            $result = $this->certificatDeNonRadiationRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Utilisateur supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/certificatDeNonRadiations/{id}/state/{state}",
     *      operationId="CertificatDeNonRadiation change state",
     *      tags={"CertificatDeNonRadiation"},
     *      security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="CertificatDeNonRadiation ID",
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
     *          description="CertificatDeNonRadiation state",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Change CertificatDeNonRadiation state",
     *      description="Change CertificatDeNonRadiation state by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiation"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/CertificatDeNonRadiation")
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
        $message = 'Changement de l\'état d\'un certificatDeNonRadiation';

        try {
            $result = $this->certificatDeNonRadiationRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Utilisateur $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/certificatDeNonRadiations-search",
     *      operationId="CertificatDeNonRadiation searching",
     *      tags={"CertificatDeNonRadiation"},
     *       security={{"JWT":{}}},
     *      summary="Return list of CertificatDeNonRadiation respecting term",
     *      description="Get all filtered certificatDeNonRadiations using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CertificatDeNonRadiation"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/CertificatDeNonRadiation")
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
        $message = 'Filtrage des certificatDeNonRadiations';

        try {
            $term = $request->term;
            $result = $this->certificatDeNonRadiationRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
