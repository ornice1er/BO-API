<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EtudeDossierRepository;
use App\Http\Requests\EtudeDossier\StoreEtudeDossierRequest;
use App\Http\Requestss\EtudeDossier\UpdateEtudeDossierRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class EtudeDossierController extends Controller
{
    protected $etudeDossierRepository;
    protected $ls;

    public function __construct(EtudeDossierRepository $etudeDossierRepository, LogService $ls)
    {
        $this->etudeDossierRepository = $etudeDossierRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/etude-dossiers",
     *      operationId="EtudeDossier list",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Return etude dossier data",
     *      description="Get all etude dossiers",
     *
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filter by status",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="commission_id",
     *          in="query",
     *          description="Filter by commission ID",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Parameter(
     *          name="member_id",
     *          in="query",
     *          description="Filter by member ID",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/EtudeDossier")
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
        $message = 'Récupération de la liste des études de dossiers';

        try {
            $result = $this->etudeDossierRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/{id}",
     *      operationId="EtudeDossier show",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Return one EtudeDossier data",
     *      description="Get EtudeDossier by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="EtudeDossier ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/EtudeDossier")
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
        $message = 'Récupération d\'une étude de dossier';

        try {
            $result = $this->etudeDossierRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Étude de dossier trouvée', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/etude-dossiers",
     *      operationId="EtudeDossier store",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Store EtudeDossier data",
     *      description="Create a new EtudeDossier",
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"commission_member_id","commission_requete_id"},
     *              @OA\Property(property="commission_member_id", type="integer", example=1),
     *              @OA\Property(property="commission_requete_id", type="integer", example=1),
     *              @OA\Property(property="mark", type="number", format="float", example=15.5),
     *              @OA\Property(property="status", type="string", example="completed"),
     *              @OA\Property(property="comment", type="string", example="Dossier conforme")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/EtudeDossier")
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
    public function store(StoreEtudeDossierRequest $request)
    {
        $message = 'Enregistrement d\'une étude de dossier';

        try {
            $result = $this->etudeDossierRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Étude de dossier créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/etude-dossiers/{id}",
     *      operationId="EtudeDossier update",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Update EtudeDossier data",
     *      description="Update an existing EtudeDossier",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="EtudeDossier ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="mark", type="number", format="float", example=15.5),
     *              @OA\Property(property="status", type="string", example="completed"),
     *              @OA\Property(property="comment", type="string", example="Dossier conforme")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/EtudeDossier")
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
    public function update(UpdateEtudeDossierRequest $request, $id)
    {
        $message = 'Mise à jour d\'une étude de dossier';

        try {
            $result = $this->etudeDossierRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Étude de dossier mise à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/etude-dossiers/{id}",
     *      operationId="EtudeDossier delete",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Delete EtudeDossier",
     *      description="Delete an EtudeDossier",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="EtudeDossier ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
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
        $message = 'Suppression d\'une étude de dossier';

        try {
            $this->etudeDossierRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'EtudeDossier ID: ' . $id]);

            return Common::success('Étude de dossier supprimée avec succès', []);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/commission/{commissionId}",
     *      operationId="EtudeDossier by commission",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Get EtudeDossier by commission",
     *      description="Get all etude dossiers for a commission",
     *
     *      @OA\Parameter(
     *          name="commissionId",
     *          in="path",
     *          description="Commission ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getByCommission($commissionId)
    {
        $message = 'Récupération des études de dossiers par commission';

        try {
            $result = $this->etudeDossierRepository->getByCommission($commissionId);
            $this->ls->trace(['action_name' => $message, 'description' => 'Commission ID: ' . $commissionId]);

            return Common::success('Études de dossiers de la commission récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/member/{memberId}",
     *      operationId="EtudeDossier by member",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Get EtudeDossier by member",
     *      description="Get all etude dossiers for a member",
     *
     *      @OA\Parameter(
     *          name="memberId",
     *          in="path",
     *          description="Member ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getByMember($memberId)
    {
        $message = 'Récupération des études de dossiers par membre';

        try {
            $result = $this->etudeDossierRepository->getByMember($memberId);
            $this->ls->trace(['action_name' => $message, 'description' => 'Member ID: ' . $memberId]);

            return Common::success('Études de dossiers du membre récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/requete/{requeteId}",
     *      operationId="EtudeDossier by requete",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Get EtudeDossier by requete",
     *      description="Get all etude dossiers for a requete",
     *
     *      @OA\Parameter(
     *          name="requeteId",
     *          in="path",
     *          description="Requete ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getByRequete($requeteId)
    {
        $message = 'Récupération des études de dossiers par requête';

        try {
            $result = $this->etudeDossierRepository->getByRequete($requeteId);
            $this->ls->trace(['action_name' => $message, 'description' => 'Requete ID: ' . $requeteId]);

            return Common::success('Études de dossiers de la requête récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/pending",
     *      operationId="Pending etude dossiers",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Get pending etude dossiers",
     *      description="Get all pending etude dossiers",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getPending()
    {
        $message = 'Récupération des études de dossiers en attente';

        try {
            $result = $this->etudeDossierRepository->getPending();
            $this->ls->trace(['action_name' => $message]);

            return Common::success('Études de dossiers en attente récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/completed",
     *      operationId="Completed etude dossiers",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Get completed etude dossiers",
     *      description="Get all completed etude dossiers",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getCompleted()
    {
        $message = 'Récupération des études de dossiers terminées';

        try {
            $result = $this->etudeDossierRepository->getCompleted();
            $this->ls->trace(['action_name' => $message]);

            return Common::success('Études de dossiers terminées récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/etude-dossiers/statistics",
     *      operationId="EtudeDossier statistics",
     *      tags={"EtudeDossier"},
     *      security={{"JWT":{}}},
     *      summary="Get etude dossier statistics",
     *      description="Get statistics for etude dossiers",
     *
     *      @OA\Parameter(
     *          name="commission_id",
     *          in="query",
     *          description="Filter by commission ID",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getStatistics(Request $request)
    {
        $message = 'Récupération des statistiques des études de dossiers';

        try {
            $commissionId = $request->get('commission_id');
            $result = $this->etudeDossierRepository->getStatistics($commissionId);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Statistiques des études de dossiers récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
} 