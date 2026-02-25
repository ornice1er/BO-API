<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CommissionRepository;
use App\Http\Requests\Commission\StoreCommissionRequest;
use App\Http\Requests\Commission\UpdateCommissionRequest;
use App\Http\Requests\Commission\AddMembersToCommissionRequest;
use App\Http\Requests\Commission\AddRequetesToCommissionRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected $commissionRepository;
    protected $ls;

    public function __construct(CommissionRepository $commissionRepository, LogService $ls)
    {
        $this->commissionRepository = $commissionRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/commissions",
     *      operationId="Commission list",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Return commission data",
     *      description="Get all commissions",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Filter by name",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="responsable",
     *          in="query",
     *          description="Filter by responsable",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filter by status",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Commission")
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
        $message = 'Récupération de la liste des commissions';

        try {
            $result = $this->commissionRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/commissions/{id}",
     *      operationId="Commission show",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Return one Commission data",
     *      description="Get Commission by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Commission ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Commission")
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
        $message = 'Récupération d\'une commission';

        try {
            $result = $this->commissionRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Commission trouvée', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/commissions",
     *      operationId="Commission store",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Store Commission data",
     *      description="Create a new Commission",
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","responsable"},
     *              @OA\Property(property="name", type="string", example="Commission d'évaluation 2024"),
     *              @OA\Property(property="arrete_file", type="string", example="arrete.pdf"),
     *              @OA\Property(property="decret_file", type="string", example="decret.pdf"),
     *              @OA\Property(property="status", type="string", example="active"),
     *              @OA\Property(property="responsable", type="string", example="Jean Dupont")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Commission")
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
    public function store(StoreCommissionRequest $request)
    {
        $message = 'Enregistrement d\'une commission';

        try {
            $result = $this->commissionRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Commission créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/commissions/{id}",
     *      operationId="Commission update",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Update Commission data",
     *      description="Update an existing Commission",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Commission ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Commission d'évaluation 2024"),
     *              @OA\Property(property="arrete_file", type="string", example="arrete.pdf"),
     *              @OA\Property(property="decret_file", type="string", example="decret.pdf"),
     *              @OA\Property(property="status", type="string", example="active"),
     *              @OA\Property(property="responsable", type="string", example="Jean Dupont")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Commission")
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
    public function update(UpdateCommissionRequest $request, $id)
    {
        $message = 'Mise à jour d\'une commission';

        try {
            $result = $this->commissionRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Commission mise à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/commissions/{id}",
     *      operationId="Commission delete",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Delete Commission",
     *      description="Delete a Commission",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Commission ID",
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
        $message = 'Suppression d\'une commission';

        try {
            $this->commissionRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'Commission ID: ' . $id]);

            return Common::success('Commission supprimée avec succès', []);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/commissions/{id}/members",
     *      operationId="Commission members",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Get Commission members",
     *      description="Get all members for a commission",
     *
     *      @OA\Parameter(
     *          name="id",
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
    public function getMembers($id)
    {
        $message = 'Récupération des membres d\'une commission';

        try {
            $result = $this->commissionRepository->getWithMembers($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'Commission ID: ' . $id]);

            return Common::success('Membres de la commission récupérés', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/commissions/{id}/requetes",
     *      operationId="Commission requetes",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Get Commission requetes",
     *      description="Get all requetes for a commission",
     *
     *      @OA\Parameter(
     *          name="id",
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
    public function getRequetes($id)
    {
        $message = 'Récupération des requêtes d\'une commission';

        try {
            $result = $this->commissionRepository->getWithRequetes($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'Commission ID: ' . $id]);

            return Common::success('Requêtes de la commission récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/commissions/{id}/members",
     *      operationId="Add members to commission",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Add members to commission",
     *      description="Add members to a commission",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Commission ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"member_ids"},
     *              @OA\Property(property="member_ids", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="is_active", type="boolean", example=true)
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function addMembers(AddMembersToCommissionRequest $request, $id)
    {
        $message = 'Ajout de membres à une commission';

        try {
            $validated = $request->validated();
            $result = $this->commissionRepository->addMembers($id, $validated['member_ids'], $validated['is_active'] ?? true);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($validated)]);

            return Common::success('Membres ajoutés à la commission avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }




    public function removeMembers(AddMembersToCommissionRequest $request, $id)
    {
        $message = 'Suppression de membres à une commission';

        try {
            $validated = $request->validated();
            $result = $this->commissionRepository->removeMembers($id, $validated['member_ids'], $validated['is_active'] ?? true);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($validated)]);

            return Common::success('Membres supprimé à la commission avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }


    /** @OA\Post(
     *      path="/commissions/{id}/requetes",
     *      operationId="Add requetes to commission",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Add requetes to commission",
     *      description="Add requetes to a commission",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Commission ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"requete_ids"},
     *              @OA\Property(property="requete_ids", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="status", type="string", example="pending")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function addRequetes(AddRequetesToCommissionRequest $request, $id)
    {
        $message = 'Ajout de requêtes à une commission';

        try {
            $validated = $request->validated();
            $result = $this->commissionRepository->addRequetes($id, $validated['requete_ids'], $validated['status'] ?? 'pending');
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($validated)]);

            return Common::success('Requêtes ajoutées à la commission avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/commissions/{id}/close",
     *      operationId="Close commission",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Close commission",
     *      description="Close a commission",
     *
     *      @OA\Parameter(
     *          name="id",
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
    public function closeCommission($id)
    {
        $message = 'Clôture d\'une commission';

        try {
            $result = $this->commissionRepository->closeCommission($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'Commission ID: ' . $id]);

            return Common::success('Commission clôturée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/commissions/active",
     *      operationId="Active commissions",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Get active commissions",
     *      description="Get all active commissions",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getActive()
    {
        $message = 'Récupération des commissions actives';

        try {
            $result = $this->commissionRepository->getActive();
            $this->ls->trace(['action_name' => $message]);

            return Common::success('Commissions actives récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/commissions/closed",
     *      operationId="Closed commissions",
     *      tags={"Commission"},
     *      security={{"JWT":{}}},
     *      summary="Get closed commissions",
     *      description="Get all closed commissions",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getClosed()
    {
        $message = 'Récupération des commissions fermées';

        try {
            $result = $this->commissionRepository->getClosed();
            $this->ls->trace(['action_name' => $message]);

            return Common::success('Commissions fermées récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
} 