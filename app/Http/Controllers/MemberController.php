<?php

namespace App\Http\Controllers;

use App\Http\Repositories\MemberRepository;
use App\Http\Requests\Members\StoreMemberRequest;
use App\Http\Requests\Members\UpdateMemberRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    protected $memberRepository;
    protected $ls;

    public function __construct(MemberRepository $memberRepository, LogService $ls)
    {
        $this->memberRepository = $memberRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/members",
     *      operationId="Member list",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Return member data",
     *      description="Get all members",
     *
     *      @OA\Parameter(
     *          name="lastname",
     *          in="query",
     *          description="Filter by lastname",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="firstname",
     *          in="query",
     *          description="Filter by firstname",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="Filter by email",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="fonction",
     *          in="query",
     *          description="Filter by fonction",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="is_active",
     *          in="query",
     *          description="Filter by active status",
     *          required=false,
     *          @OA\Schema(type="boolean")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
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
        $message = 'Récupération de la liste des membres';

        try {
            $result = $this->memberRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/members/{id}",
     *      operationId="Member show",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Return one Member data",
     *      description="Get Member by ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Member ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
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
        $message = 'Récupération d\'un membre';

        try {
            $result = $this->memberRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Membre trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/members",
     *      operationId="Member store",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Store Member data",
     *      description="Create a new Member",
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"lastname","firstname","email","fonction"},
     *              @OA\Property(property="lastname", type="string", example="Dupont"),
     *              @OA\Property(property="firstname", type="string", example="Jean"),
     *              @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *              @OA\Property(property="fonction", type="string", example="Expert"),
     *              @OA\Property(property="is_active", type="boolean", example=true)
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
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
    public function store(StoreMemberRequest $request)
    {
        $message = 'Enregistrement d\'un membre';

        try {
            $result = $this->memberRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Membre créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/members/{id}",
     *      operationId="Member update",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Update Member data",
     *      description="Update an existing Member",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Member ID",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="lastname", type="string", example="Dupont"),
     *              @OA\Property(property="firstname", type="string", example="Jean"),
     *              @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *              @OA\Property(property="fonction", type="string", example="Expert"),
     *              @OA\Property(property="is_active", type="boolean", example=true)
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
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
    public function update(UpdateMemberRequest $request, $id)
    {
        $message = 'Mise à jour d\'un membre';

        try {
            $result = $this->memberRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Membre mis à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/members/{id}",
     *      operationId="Member delete",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Delete Member",
     *      description="Delete a Member",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Member ID",
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
        $message = 'Suppression d\'un membre';

        try {
            $this->memberRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'Member ID: ' . $id]);

            return Common::success('Membre supprimé avec succès', []);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/members/{id}/commissions",
     *      operationId="Member commissions",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Get Member commissions",
     *      description="Get all commissions for a member",
     *
     *      @OA\Parameter(
     *          name="id",
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
    public function getCommissions($id)
    {
        $message = 'Récupération des commissions d\'un membre';

        try {
            $result = $this->memberRepository->getWithCommissions($id);
            $this->ls->trace(['action_name' => $message, 'description' => 'Member ID: ' . $id]);

            return Common::success('Commissions du membre récupérées', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/members/active",
     *      operationId="Active members",
     *      tags={"Member"},
     *      security={{"JWT":{}}},
     *      summary="Get active members",
     *      description="Get all active members",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getActive()
    {
        $message = 'Récupération des membres actifs';

        try {
            $result = $this->memberRepository->getActive();
            $this->ls->trace(['action_name' => $message]);

            return Common::success('Membres actifs récupérés', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
} 