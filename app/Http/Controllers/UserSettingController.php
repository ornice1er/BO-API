<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserSettingRepository;
use App\Http\Requests\UserSetting\UpdateUserSettingRequest;
use App\Models\UserSetting;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserSettingController
{
    /**
     * Le repository des UserSettings.
     *
     * @var UserSettingRepository
     */
    protected $userSettingRepository;

    protected $ls;

    public function __construct(UserSettingRepository $userSettingRepository, LogService $ls)
    {
        $this->userSettingRepository = $userSettingRepository;
        $this->ls = $ls;
    }

    /**
     * @OA\Get(
     *      path="/user-settings",
     *      operationId="UserSetting list",
     *      tags={"UserSetting"},
     *      security={{"JWT":{}}},
     *      summary="Retourne la liste des UserSettings",
     *      description="Obtenir tous les paramètres des utilisateurs",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Opération réussie",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserSetting"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserSetting")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Requête invalide"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Session expirée"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Non trouvé"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur serveur"
     *      )
     * )
     */
    public function index(Request $request)
    {
        $message = 'Récupération de la liste des paramètres utilisateur';

        try {
            $result = $this->userSettingRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Récupération des paramètres utilisateur réussie', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Put(
     *      path="/user-settings",
     *      operationId="UserSetting update",
     *      tags={"UserSetting"},
     *      security={{"JWT":{}}},
     *      summary="Mettre à jour les paramètres utilisateur",
     *      description="Mise à jour des paramètres utilisateur",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Opération réussie",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserSetting"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserSetting")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="Requête de mise à jour",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserSettingUpdate")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Requête invalide"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Session expirée"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Non trouvé"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur serveur"
     *      )
     * )
     */
    public function update(UpdateUserSettingRequest $request)
    {
        $message = 'Mise à jour des paramètres utilisateur';

        try {
            $result = $this->userSettingRepository->makeUpdate($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Paramètres utilisateur mis à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }
}
