<?php

namespace App\Http\Controllers;

use App\Http\Repositories\MenuRepository;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MenuController
{
    /**
     * The department being queried.
     *
     * @var Menu
     */
    protected $menuRepository;

    protected $ls;

    public function __construct(MenuRepository $menuRepository, LogService $ls)
    {
        $this->menuRepository = $menuRepository;
        $this->ls = $ls;

    }

    /** @OA\Get(
     *      path="/menus",
     *      operationId="Menu list",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
     *      summary="Return Menu data ",
     *      description="Get all menus",
     *
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Can used for filtering data by name",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *  @OA\Parameter(
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
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Menu"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Menu")
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
    public function index(Request $request)
    {
        $message = 'Recupération de la liste des départements';
        try {
            $result = $this->menuRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Recupération de la liste des départements', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/menus/{id}",
     *      operationId="Menu show",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
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
     *      summary="Return one Menu data ",
     *      description="Get  Menu by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Menu"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Menu")
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
    public function show($id)
    {

        $message = 'Récupécuration de département ';

        try {

            $result = $this->menuRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('département trouvé', $result);
        } catch (\Throwable $th) {
            // return $th->status();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/menus",
     *      operationId="Menu store",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
     *      summary="store Menu data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Menu"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Menu")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/MenuCreate")
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
    public function store(StoreMenuRequest $request)
    {
        $message = 'Enregistrement d\un départment';

        try {

            $result = $this->menuRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Départment créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Put(
     *      path="/menus/{id}",
     *      operationId="Menu update",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
     *      summary="update one Menu data ",
     *      description="",
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

     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Menu"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Menu")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/MenuCreate")
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
    public function update(UpdateMenuRequest $request, $id)
    {
        $message = 'Mise à jour d\'un département ';

        try {
            $result = $this->menuRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un département effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Delete(
     *      path="/menus/{id}",
     *      operationId="Menu Delete",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
     *      summary="delete Menu data ",
     *      description="",
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
     *)
     */
    public function destroy($id)
    {
        $message = 'Suppression d\'un département';

        try {
            $recup = $this->menuRepository->get($id);
            $result = $this->menuRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Département supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/menus/{id}/state/{state}",
     *      operationId="Menu change state",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
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
     *
     *  @OA\Parameter(
     *      name="state",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Apply new status for one Menu ",
     *      description="Get  Menu by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Menu"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Menu")
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
    public function changeState($id, $state)
    {
        $message = 'Status d\'un département';

        try {
            $result = $this->menuRepository->setStatus($id, $state);

            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Département $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/menus-search",
     *      operationId="Menu searching",
     *      tags={"Menu"},
     *     security={{"JWT":{}}},
     *      summary="return list of Menu respecting term ",
     *      description="Get all filtered menus using  term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Menu"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Menu")
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
     *)
     */
    public function search(Request $request)
    {
        $message = 'Filtrage';

        try {
            $term = $request->term;
            $result = $this->menuRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Flitrage éffectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }
}
