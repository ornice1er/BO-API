<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserAuthRepository;
use App\Http\Requests\File\UploadRequest;
use App\Http\Requests\UserAuth\AddPushTokenRequest;
use App\Http\Requests\UserAuth\ChangeFirstPasswordRequest;
use App\Http\Requests\UserAuth\ChangePasswordRequest;
use App\Http\Requests\UserAuth\GetUserPermissionRequest;
use App\Http\Requests\UserAuth\LoginRequest;
use App\Http\Requests\UserAuth\RecoveryPasswordRequest;
use App\Http\Requests\UserAuth\ResetPasswordRequest;
use App\Http\Requests\UserAuth\SendResetPasswordLinkRequest;
use App\Http\Requests\UserAuth\UpdateProfilRequest;
use App\Models\User;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Str;

/**
 * @OA\Tag(
 *     name="User Authentication",
 *     description="API Endpoints of User Authentication"
 * )
 */
class UserAuthController extends Controller
{
    /**
     * The Setting repository being queried.
     *
     * @var UserAuthRepository
     */
    protected $userauthRepository;

    protected $ls;

    public function __construct(UserAuthRepository $userAuthRepository, LogService $ls)
    {
        $this->userauthRepository = $userAuthRepository;
        $this->ls = $ls;
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Log in a user",
     *     description="Authenticate a user and return a token",
     *     tags={"User Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1Qi...")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(LoginRequest $request)
    {
        $message = 'Tentative de connexion';

        try {

            $result = $this->userauthRepository->login($request->validated());
            // $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('', $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            // Vérifier si c'est une exception JsonResponseException
            return $e->render();
        } catch (\Throwable $th) {
            // $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Get authenticated user",
     *     description="Get the currently authenticated user's information",
     *     tags={"User Authentication"},
     *     security={{"JWT":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User data retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function user()
    {
        $message = 'Récupération des données utilisateur';

        try {
            $result = $this->userauthRepository->user();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Données utilisateur récupérées avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/user-permissions",
     *     summary="User Permission",
     *     description="User Permission",
     *     tags={"User Permission"},
     *     security={{"JWT":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/GetUserPermissionRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Role")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function userPermission(GetUserPermissionRequest $request)
    {
        $message = 'Recuperation des permissions';

        try {
            $result = $this->userauthRepository->user_permissions($request, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Recuperation des permissions', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/change-password",
     *     summary="Change password",
     *     description="Change the authenticated user's password",
     *     tags={"User Authentication"},
     *     security={{"JWT":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ChangePasswordRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $message = 'Changement de mot de passe';

        try {
            $result = $this->userauthRepository->changePassword($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mot de passe changé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/change-first-password",
     *     summary="Change password at first time",
     *     description="user must change his password during first connexion",
     *     tags={"User Authentication"},
     * security={{"JWT":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ChangeFirstPasswordParams")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1Qi...")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function changeFirstPassword(ChangeFirstPasswordRequest $request)
    {
        $message = 'Changement de mot de passe initial';

        try {
            $result = $this->userauthRepository->changeFirstPassword($request->validated());
            // $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Changement mot de passe effectué avec succès', $result);
        } catch (\Throwable $th) {
            // $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/user-update",
     *      operationId="User data update",
     *      tags={"User Authentication"},
     *      security={{"JWT":{}}},
     *      summary="Update User data",
     *      description="User can update his profil data",
     *
     *      @OA\RequestBody(
     *          description="Body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProfilUpdate")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/User"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/User")
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
    public function update(UpdateProfilRequest $request)
    {
        $message = 'Mise à jour du profil';

        try {
            $result = $this->userauthRepository->update($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour du profil effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/user-notify",
     *      operationId="User notify",
     *      tags={"User Authentication"},
     *      security={{"JWT":{}}},
     *      summary="Notify User data",
     *      description="User can update his profil data",
     *
     *      @OA\RequestBody(
     *          description="Body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserNotify")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserNotify"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserNotify")
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
    public function notify(Request $request)
    {
        $message = 'Notification utilisateur';

        try {
            $result = $this->userauthRepository->notify($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Notification utilisateur envoyé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     *     path="/logout",
     *     summary="Sign Out authenticated user",
     *     description="Close user session",
     *     tags={"User Authentication"},
     *     security={{"JWT":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User data retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        $message = 'Déconnexion de l\'utilisateur';

        try {
            $result = $this->userauthRepository->logout($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Utilisateur déconnecté avec succès.', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/reset-password",
     *     summary="Reset password using email",
     *     description="Administrator can reset password as default and user can change it at first connexion with new password",
     *     tags={"User Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordParams")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1Qi...")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $message = 'Réinitialisation du mot de passe';

        try {
            $result = $this->userauthRepository->resetPassword($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mot de passe réinitialisé avec succès', $result->validated());
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Reset password token generation",
     *     description="User can reset his password when he forgot id by generating a token and got link by email",
     *     tags={"User Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordLinkParams")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1Qi...")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function sendResetPasswordLink(SendResetPasswordLinkRequest $request)
    {
        $message = 'Envoi du lien de réinitialisation du mot de passe';

        try {
            $result = $this->userauthRepository->sendResetPasswordLink($request->validated());
            //  $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Email de réinitialisation envoyé avec succès', $result);
        } catch (\Throwable $th) {
            // $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/recovery-password",
     *     summary="Reset password by token",
     *     description="User can reset his password",
     *     tags={"User Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/RecoveryPasswordParams")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1Qi...")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function recoveryPassword(RecoveryPasswordRequest $request)
    {
        $message = 'Récupération du mot de passe';

        try {
            $result = $this->userauthRepository->recoveryPassword($request->validated());
            // $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mot de passe réinitialisé avec succès', $result);
        } catch (\Throwable $th) {
            //  $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/upload-file",
     *     summary="Upload file",
     *     description="UUser can upload file",
     *     tags={"Upload File"},
     *      security={{"JWT":{}}},
     *
     *     @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/UploadFile"),
     *                  }
     *              )
     *              )
     *      ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="file_name", type="string"),
     *              @OA\Property(property="full_url", type="string"),
     *              @OA\Property(property="full_path", type="string"),
     *
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function uploadFile(Request $request)
    {
        $message = 'Upload de fichier';

        // $validator = Validator::make($request->all(), (new UploadRequest)->rules());
        // if ($validator->fails()) {
        //     $this->ls->trace(data: ['action_name' => $message, 'description' => json_encode($validator->errors())]);
        // }

        try {
            if (request()->hasFile('input')) {
                $file = request()->file('input');
                $extension = $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName().'__'.Str::random(20).'_'.date('His').rand(1, 999999).'.'.$extension;

                $path = $request->path;

                $full_url = Storage::disk('s3')->url($file->storeAs($path, $fileName, 's3'));

                return Common::success('Fichier uploadé avec succès', [
                    'file_name' => $fileName,
                    'full_url' => $full_url,
                    'full_path' => $path.'/'.$fileName,
                ]);
            } else {
                $this->ls->trace(data: ['action_name' => $message, 'description' => 'file not found']);

            }

        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/delete-file",
     *     summary="delete file",
     *     description="User can delete file",
     *     tags={"Upload File"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/DeleteFile")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function deleteFile(Request $request)
    {
        $message = 'Suppression de fichier';

        try {
            $path = $request->path;

            Storage::disk('s3')->delete($path);

            return Common::success('Fichier supprimé avec succès', []);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/user-push-token",
     *     summary="add push token",
     *     description="User recieve notification",
     *     tags={"User Authentication"},
     *     security={{"JWT":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserPushCreate")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful setting push",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function addPushToken(AddPushTokenRequest $request)
    {
        $message = 'Ajout de push token notifications';

        try {
            $result = $this->userauthRepository->addPushToken($request->validated());

            return Common::success('Ajout de push token', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     *     path="/user-push-token-delete/{id}",
     *     summary="remove push token",
     *     description="User token delete",
     *     tags={"User Authentication"},
     *     security={{"JWT":{}}},
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User ID",
     *          required=true
     *      ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function deletePushToken($id)
    {
        $message = 'Suppression de push token notifications';

        try {
            $result = $this->userauthRepository->deletePushToken($id);

            return Common::success('Suppression de push notification', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }
}
