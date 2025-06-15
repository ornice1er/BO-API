<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CountryRepository;
use App\Http\Requests\Country\StoreCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;
use App\Models\Country;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CountryController
{
    /**     * The country being queried.
     *
     * @var Country
     */
    protected $countryRepository;

    protected $ls;

    public function __construct(CountryRepository $countryRepository, LogService $ls)
    {
        $this->countryRepository = $countryRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/countries",
     *      operationId="Country list",
     *      tags={"Country"},
     *     security={{"JWT":{}}},
     *      summary="Return Country data ",
     *      description="Get all countries",
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
     *         @OA\JsonContent(ref="#/components/schemas/Country"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Country")
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
        $message = 'Recupération de la liste des pays';
        try {
            $result = $this->countryRepository->getAll($request);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/countries/{id}",
     *      operationId="Country show",
     *      tags={"Country"},
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
     *      summary="Return one Country data ",
     *      description="Get  country by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Country"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Country")
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
        $message = 'Récupécuration de pays';

        try {

            $result = $this->countryRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Pays trouvé', $result);
        } catch (\Throwable $th) {
            // return $th->status();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/countries",
     *      operationId="Country store",
     *      tags={"Country"},
     *     security={{"JWT":{}}},
     *      summary="store Country data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Country"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Country")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/CountryCreate")
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
    public function store(StoreCountryRequest $request)
    {
        $message = 'Enregistrement de pays';

        try {
            $result = $this->countryRepository->makeStore($request->validated());

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Pays crée  avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Put(
     *      path="/countries/{id}",
     *      operationId="Country update",
     *      tags={"Country"},
     *     security={{"JWT":{}}},
     *      summary="update one Country data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/Country"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Country")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/CountryCreate")
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
    public function update(UpdateCountryRequest $request, $id)
    {
        $message = 'Mise à jour d\'un pays';

        try {
            $result = $this->countryRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un pays effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/countries/{id}",
     *      operationId="Country Delete",
     *      tags={"Country"},
     *     security={{"JWT":{}}},
     *      summary="delete Country data ",
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
        $message = 'Suppression d\'un pays';

        try {
            $recup = $this->countryRepository->get($id);
            $result = $this->countryRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Pays supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/countries/{id}/state/{state}",
     *      operationId="Country change state",
     *      tags={"Country"},
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
     *      summary="Apply new status for one Country ",
     *      description="Get  country by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Country"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Country")
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
        $message = 'Status d\'un pays';

        try {
            $result = $this->countryRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Pays $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/countries-search",
     *      operationId="Country searching",
     *      tags={"Country"},
     *     security={{"JWT":{}}},
     *      summary="return list of Country respecting term ",
     *      description="Get all filtered countries using  term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Country"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Country")
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
            $result = $this->countryRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage éffectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
