<?php

namespace App\Http\Controllers;

use App\Http\Repositories\DistrictRepository;
use App\Http\Requests\District\StoreDistrictRequest;
use App\Http\Requests\District\UpdateDistrictRequest;
use App\Models\District;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DistrictController
{
    /**
     * The district being queried.
     *
     * @var District
     */
    protected $districtRepository;

    protected $ls;

    public function __construct(DistrictRepository $districtRepository, LogService $ls)
    {
        $this->districtRepository = $districtRepository;
        $this->ls = $ls;

    }

    /** @OA\Get(
     *      path="/districts",
     *      operationId="District list",
     *      tags={"District"},
     *     security={{"JWT":{}}},
     *      summary="Return District data ",
     *      description="Get all districts",
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
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/District"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/District")
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
        $message = 'Recupération de la liste des arrondissements ';

        try {
            $result = $this->districtRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Recupération de la liste des arrondissements', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/districts/{id}",
     *      operationId="District show",
     *      tags={"District"},
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
     *      summary="Return one District data ",
     *      description="Get  District by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/District"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/District")
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
        $message = "Recupération d'arrondissement ";

        try {

            $result = $this->districtRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Arrondissement trouvé', $result);
        } catch (\Throwable $th) {
            // return $th->status();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/districts",
     *      operationId="District store",
     *      tags={"District"},
     *     security={{"JWT":{}}},
     *      summary="store District data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/District"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/District")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/DistrictCreate")
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
    public function store(StoreDistrictRequest $request)
    {
        $message = "Enregistrement d'un arrondissment ";

        try {

            $result = $this->districtRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Arrondissement créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Put(
     *      path="/districts/{id}",
     *      operationId="District update",
     *      tags={"District"},
     *     security={{"JWT":{}}},
     *      summary="update one District data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/District"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/District")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/DistrictCreate")
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
    public function update(UpdateDistrictRequest $request, $id)
    {
        $message = "Mise à jour d\'un arrondissement";

        try {
            $result = $this->districtRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un arrondissement effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Delete(
     *      path="/districts/{id}",
     *      operationId="District Delete",
     *      tags={"District"},
     *     security={{"JWT":{}}},
     *      summary="delete District data ",
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
        $message = "Suppression d\'un arrondissement";

        try {
            $recup = $this->districtRepository->get($id);

            $result = $this->districtRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Arrondissement supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/districts/{id}/state/{state}",
     *      operationId="District change state",
     *      tags={"District"},
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
     *      summary="Apply new status for one District ",
     *      description="Get  District by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/District"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/District")
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
        $message = "Status d\'un arrondissement";

        try {
            $result = $this->districtRepository->setStatus($id, $state);

            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Arrondissement $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/districts-search",
     *      operationId="District searching",
     *      tags={"District"},
     *     security={{"JWT":{}}},
     *      summary="return list of District respecting term ",
     *      description="Get all filtered districts using  term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/District"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/District")
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
            $result = $this->districtRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Flitrage éffectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }
}
