<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SettingRepository;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SettingController
{
    /**
     * The Setting repository being queried.
     *
     * @var SettingRepository
     */
    protected $settingRepository;

    protected $ls;

    public function __construct(SettingRepository $settingRepository, LogService $ls)
    {
        $this->settingRepository = $settingRepository;
        $this->ls = $ls;

    }

    /**
     * @OA\Get(
     *      path="/settings",
     *      operationId="Setting list",
     *      tags={"Setting"},
     *      summary="Return Settings data",
     *      description="Get all settings",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Setting"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Setting")
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
        $message = 'Récupération de la liste des paramètres';

        try {
            $result = $this->settingRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Récupération de la liste des paramètres', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *      path="/settings",
     *      operationId="Setting update",
     *      tags={"Setting"},
     *      summary="Update one Setting data",
     *      description="",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Setting"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Setting")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="Body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/SettingUpdate")
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
    public function update(UpdateSettingRequest $request)
    {
        $message = 'Mise à jour du paramètre';

        try {
            $result = $this->settingRepository->makeUpdate($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour du paramètre effectuée avec succès', $result);
        } catch (\Throwable $th) {

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }
}
