<?php

namespace App\Http\Repositories;

use App\Exceptions\JsonResponseException;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\DefaultNotification;
use App\Notifications\ElectionPrClosedNotification;
use App\Services\OTPService;
use App\Traits\Repository;
use App\Utilities\Common;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use JWTAuth;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Twilio\TwilioChannel;

class UserAuthRepository
{
    use Repository;

    protected $otpService;



    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
    }

    /**
     * Login
     */
    public function login($data)
    {
     

        info(request()->ip());
        // Tente l'authentification
        $exp = $data['device'] == 'web' ? Carbon::now()->addSeconds(3600) : Carbon::now()->addSeconds(86400);
        unset($data['device']);
        unset($data['new_connexion_canal']);
        unset($data['canal_value']);

        $token = Auth::guard('api')->attempt($data, ['exp' => $exp->timestamp]);
        if (! $token) {
            throw new JsonResponseException([
                'message' => 'Email ou mot de passe incorrect',
                'success' => false,
                'data' => null,
                'warning' => 'Vérifiez l\'adresse e-mail fournie ou le mot de passe',
            ], 401);

        }

      
        $user = Auth::guard(name: 'api')->user();
        // if (($user->is_first_connexion || $user->browser != $browser || ($user->browser == $browser && $user->ip_address != $ipAddress)) && ! request()->has('new_connexion_canal')) {

        //     throw new JsonResponseException([
        //         'message' => 'Utilisateur reconnu, mais origine de connexion différente',
        //         'success' => false,
        //         'data' => null,
        //         'warning' => 'Veuillez fournir le canal par lequel vous voudriez recevoir le code OTP',
        //     ], 200);

        // } elseif (request()->has('new_connexion_canal') || $user->settings?->use_2FA) {

        //     if (! request()->has('code_otp')) {
        //         $code = random_int(100000, 999999);
        //         $canal = request()->new_connexion_canal ?? $user->settings?->mode_2FA;

        //         switch ($canal) {
        //             case 'SMS':
        //                 if ($this->otpService->sendSMSOTP(request()->canal_value ?? $user->phone, $code) === false) {
        //                     throw new HttpResponseException(Common::error("Echec d'envoi du code par SMS", []));
        //                 }

        //                 break;
        //             case 'WHATSAPP':
        //                 if ($this->otpService->sendWhatsappSms(request()->canal_value ?? $user->phone, $code) === false) {
        //                     throw new HttpResponseException(Common::error("Echec d'envoi du code par WHATSAPP", []));
        //                 }

        //                 break;
        //             case 'EMAIL':
        //                 if ($this->otpService->sendMailOTP(request()->canal_value ?? $user->email, $code) == false) {
        //                     throw new HttpResponseException(Common::error("Echec d'envoi du code par EMAIL", []));
        //                 }
        //                 break;

        //             default:
        //                 throw new HttpResponseException(Common::error('Aucun mode 2FA choisi', []));
        //                 break;
        //         }

        //         return [
        //             'message' => 'Un code otp a été envoyé par '.$canal,
        //         ];
        //     } else {
        //         if ($user->code_otp != request()->code_otp) {
        //             throw new HttpResponseException(Common::error('Code OTP erroné', []));
        //         }
        //     }
        // }
        //  $user->is_first_connexion = false;
        $user->save();

        // Si l'authentification réussit
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expired_at' => Carbon::parse($exp)
                ->timezone('Africa/Porto-Novo')
                ->toDateTimeString(),
        ];

    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user()
    {

        $user = User::with(['roles.permissions','userPrestations.prestation.uniteAdmin','userPrestations.prestation.startPoints','userPrestations.prestation.startPoint2', 'settings','agent.uniteAdmin'])->whereId(Auth::id())->first();

        return $user;

    }

    public function user_permissions(Request $request, $data)
    {
        $projectId = $request->input('project_id');
        $label_names = $request->input('label_names'); // Ensure it's an array

        $up = UserProject::with([
            'roles',
            'roles.permissions' => function ($query) use ($label_names) {
                $query->whereIn('label_name', $label_names);
            },
        ])
            ->where('user_id', Auth::id())
            ->where('project_id', $projectId)
            ->first();

        return $up ? $up->roles : null;

    }

    public function changePassword($data)
    {
        $user = User::find(Auth::id());
        if (! Hash::check($data['old_password'], $user->password)) {
            throw new HttpResponseException(Common::failedValidation('Ancien mot de passe incorrect'));
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        return $user;
    }

    public function changeFirstPassword($data)
    {
        $user = User::find(Auth::id());

        $user->update([
            'password' => Hash::make($data['password']),
            'is_first_connexion' => 0,
        ]);

        return $user;
    }

    public function update($data)
    {
        $user = User::find(Auth::id());

        if (request()->hasFile('photo')) {
            FileStorage::deleteFile('public', $user->filename, 'avatars');
            $filename = FileStorage::setFile('public', request()->file('photo'), 'avatars', Str::slug($data['lastname'].'.'.$data['firstname'].'.'.time()));
            $data['photo'] = 'avatars/'.$filename;
        }

        $user->update($data);

        return $user;
    }

    public function logout(Request $request)
    {

        try {
            // Adds token to blacklist.
            $forever = true;
            JWTAuth::parseToken()->invalidate($forever);

            return null;

        } catch (TokenExpiredException $exception) {
            return response()->json([
                'error' => true,
                'message' => trans('auth.token.expired'),

            ], 401);
        } catch (TokenInvalidException $exception) {
            return response()->json([
                'error' => true,
                'message' => trans('auth.token.invalid'),
            ], 401);

        } catch (JWTException $exception) {
            return response()->json([
                'error' => true,
                'message' => trans('auth.token.missing'),
            ], 500);
        }
        $request->user()->currentAccessToken()->delete();

    }

    public function resetPassword($data)
    {
        $user = User::where('email', $data['email'])->first();

        $user->update([
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            'is_first_connexion' => true,
        ]);

        return $user;
    }

    public function sendResetPasswordLink($data)
    {
        $user = User::where('email', $data['email'])->first();
        $token = Str::random(64);
        $check = PasswordReset::where('email', $data['email'])->first();
        if ($check) {
            $check->token = $token;
            $check->created_at = Carbon::now();
            $check->save();
        } else {
            PasswordReset::create([
                'email' => $data['email'],
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

        }

        Mailer::sendSimple('emails.reset_password', ['user' => $user, 'token' => $token], 'Réinitialisation de mot de passe', $user->name, $user->email);

        return null;
    }

    public function recoveryPassword($data)
    {
        $checkToken = PasswordReset::where('token', $data['token'])->first();

        $user = User::whereEmail($checkToken->email);
        $user = $user->update(['password' => Hash::make($data['password'])]);
        $q = 'DELETE FROM password_reset_tokens where token = ?';
        DB::delete($q, [$data['token']]);

        return $user;
    }

    public function getBrowser(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        $agent = new \Jenssegers\Agent\Agent;
        $agent->setUserAgent($userAgent);

        return $agent->browser();
    }

    public function notify($request)
    {

        $canal = $request->canal;
        if (array_search('PUSH', $canal) != null) {
            $canal[array_search('PUSH', $canal)] = FcmChannel::class;
        }
        if (array_search('SMS', $canal) != null) {
            $canal[array_search('SMS', $canal)] = TwilioChannel::class;
        }
        info($canal);

        try {
            switch ($request->category) {
                case 'election-pr-closed':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new ElectionPrClosedNotification($canal, $request->title, $request->content));
                    break;
                case 'next-activity':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new ElectionPrClosedNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-competion-register-closing':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-competion-step-closing':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'change-pr-state':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'etablissement-election-pr-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-pr-question':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-evaluation-re':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-pr-question-animatrice':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-pr-question-re':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-pr-question':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-status-pr-question-animatrice':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-status-pr-question':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'feedback-status-pr-question-responsable':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-plan-action':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-point-capitalisation':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-supervision-activite':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'add-monthly-report':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'comment-monthly-report':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 're-comment-monthly-report':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-gap-negative':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'alert-officer-payment':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-animarice-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-animarice-validated':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-re-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'evaluation-pr-done':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;
                case 'prime-pr':
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content, $request->file));
                    break;
                case 'change-status-agent':
                    info($request->users);
                    $users = User::whereIn('id', $request->users)->get();
                    Notification::send($users, new DefaultNotification($canal, $request->title, $request->content));
                    break;

                default:
                    // code...
                    break;
            }

            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addPushToken($data)
    {
        return User::find($data['user_id'])->update(['push_token' => $data['push_token']]);
    }

    public function deletePushToken($id)
    {
        return User::find($id)->update(['push_token' => null]);
    }
}
