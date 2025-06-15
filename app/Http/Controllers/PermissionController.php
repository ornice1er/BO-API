<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PermissionRepository;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PermissionController
{
    /**
     * The country being queried.
     *
     * @var Permission
     */
    protected $permissionRepository;

    protected $ls;

    public function __construct(PermissionRepository $permissionRepository, LogService $ls)
    {
        $this->permissionRepository = $permissionRepository;
        $this->ls = $ls;

    }

    /** @OA\Get(
     *      path="/permissions",
     *      operationId="Permission list",
     *      tags={"Permission"},
     *      security={{"JWT":{}}},
     *      summary="Return Permission data ",
     *      description="Get all permissions",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Can used for filtering data by name",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *   @OA\Parameter(
     *      name="with",
     *      in="query",
     *      description="Ecrire les relations à ajouter à la récupération",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Permission"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Permission")
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
        $message = 'Récupération de la liste des permissions';
        try {

            $result = $this->permissionRepository->getAll($request);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/permissions/{id}",
     *      operationId="Permission show",
     *      tags={"Permission"},
     *      security={{"JWT":{}}},
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Return one Permission data",
     *      description="Get permission by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Permission"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Permission")
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
     * )
     */
    public function show($id)
    {
        $message = 'Récupération de permission';

        try {
            $result = $this->permissionRepository->get($id);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Permission trouvée', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/permissions",
     *      operationId="Permission store",
     *      tags={"Permission"},
     *      security={{"JWT":{}}},
     *      summary="Store Permission data",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Permission"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Permission")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/PermissionCreate")
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
     * )
     */
    public function store(StorePermissionRequest $request)
    {
        $message = 'Enregistrement de permission';

        try {
            $result = $this->permissionRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Permission créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/permissions/{id}",
     *      operationId="Permission update",
     *      tags={"Permission"},
     *      security={{"JWT":{}}},
     *      summary="Update one Permission data",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="ID of the permission",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Permission"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Permission")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/PermissionCreate")
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
     * )
     */
    public function update(UpdatePermissionRequest $request, $id)
    {
        $message = 'Mise à jour d\'une permission';

        try {
            $result = $this->permissionRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'une permission effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/permissions/{id}",
     *      operationId="Permission Delete",
     *      tags={"Permission"},
     *      security={{"JWT":{}}},
     *      summary="Delete Permission data",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="ID of the permission",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/DeleteResponseData"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/DeleteResponseData")
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
     * )
     */
    public function destroy($id)
    {
        $message = 'Suppression d\'une permission';

        try {
            $recup = $this->permissionRepository->get($id);

            $result = $this->permissionRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Permission supprimée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/permissions-search",
     *      operationId="Permission searching",
     *      tags={"Permission"},
     *      security={{"JWT":{}}},
     *      summary="Return list of Permissions respecting the search term",
     *      description="Get all filtered permissions using a search term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Permission"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Permission")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
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
     * )
     */
    public function search(Request $request)
    {
        $message = 'Filtrage des permissions';

        try {
            $term = $request->term;
            $result = $this->permissionRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
