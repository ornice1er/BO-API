<?php

namespace App\Http\Controllers;

use App\Http\Repositories\VillageRepository;
use App\Http\Requests\Village\StoreVillageRequest;
use App\Http\Requests\Village\UpdateVillageRequest;
use App\Models\Village;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VillageController
{
    /**
     * The village being queried.
     *
     * @var Village
     */
    protected $villageRepository;

    protected $ls;

    public function __construct(VillageRepository $villageRepository, LogService $ls)
    {
        $this->villageRepository = $villageRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/villages",
     *      operationId="Village list",
     *      tags={"Village"},
     *      security={{"JWT":{}}},
     *      summary="Return Village data",
     *      description="Get all villages",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Can be used for filtering data by name",
     *          required=false,
     *
     *          @OA\Schema(
     *               type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Village"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Village")
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
        $message = 'Récupération de la liste des village';
        try {
            $result = $this->villageRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Récupération de la liste des villages', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/villages/{id}",
     *      operationId="Village show",
     *      tags={"Village"},
     *     security={{"JWT":{}}},
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="ID of the Village",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Return one Village data",
     *      description="Get Village by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Village"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Village")
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
        $message = 'Récupération d\'un village';

        try {
            $result = $this->villageRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Village trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/villages",
     *      operationId="Village store",
     *      tags={"Village"},
     *      security={{"JWT":{}}},
     *      summary="Store Village data",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Village"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Village")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/VillageCreate")
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
    public function store(StoreVillageRequest $request)
    {
        $message = 'Enregistrement d\'un village';
        try {
            $result = $this->villageRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Village créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/villages/{id}",
     *      operationId="Village update",
     *      tags={"Village"},
     *      security={{"JWT":{}}},
     *      summary="Update one Village data",
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
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Village"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Village")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/VillageCreate")
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
    public function update(UpdateVillageRequest $request, $id)
    {
        $message = 'Mise à jour d\'un village';

        try {
            $result = $this->villageRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un village effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/villages/{id}",
     *      operationId="Village Delete",
     *      tags={"Village"},
     *      security={{"JWT":{}}},
     *      summary="Delete Village data",
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
     *         description="Successful operation",
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
        $message = 'Suppression d\'un village';

        try {
            $recup = $this->villageRepository->get($id);

            $result = $this->villageRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Village supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/villages/{id}/state/{state}",
     *      operationId="Village change state",
     *      tags={"Village"},
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
     *      summary="Apply new status for one Village",
     *      description="Get Village by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Village"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Village")
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
    public function changeState($id, $state)
    {
        $message = 'Status d\'un village';

        try {
            $result = $this->villageRepository->setStatus($id, $state);

            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Village $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/villages-search",
     *      operationId="Village searching",
     *      tags={"Village"},
     *      security={{"JWT":{}}},
     *      summary="Return list of Village respecting term",
     *      description="Get all filtered villages using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Village"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Village")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/TermSearch")
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function search(Request $request)
    {
        $message = 'Filtrage';

        try {
            $term = $request->term;
            $result = $this->villageRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }
}
