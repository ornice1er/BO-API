<?php

namespace App\Services;

use App\Models\User;
use App\Models\Verification;
use App\Utilities\Mailer;

class OTPService
{
    protected $twService;

    protected $errorMsg;

    public function __construct(TwilioService $twService)
    {
        $this->twService = $twService;
        $this->errorMsg = 'Un code est déjà envoyé à ce destinaire par numéro et/ou email. Ce code expirera dans 5 min';
    }

    public function createOTPExpirationTime($email, $code)
    {
        $array = [
            'code' => $code,
            'email' => $email,
            'expired_at' => date('Y-m-d H:i:s', strtotime('+5 minute', strtotime('now'))),
        ];
        $verification = Verification::where('email', $email)->first();
        if ($verification) {
            $verification->update($array);
        } else {
            Verification::create($array);
        }

        return true;
    }

    public function createOTPExpirationTime1($phone, $code)
    {
        $array = [
            'code' => $code,
            'phone' => $phone,
            'sms' => true,
            'expired_at' => date('Y-m-d H:i:s', strtotime('+5 minute', strtotime('now'))),
        ];
        $verification = Verification::where('phone', $phone)->first();
        if ($verification) {
            $verification->update($array);
        } else {
            Verification::create($array);
        }

        return true;
    }

    public function createOTPExpirationTime2($phone, $code)
    {
        $array = [
            'code' => $code,
            'phone' => $phone,
            'whatsapp' => true,
            'expired_at' => date('Y-m-d H:i:s', strtotime('+5 minute', strtotime('now'))),
        ];
        $verification = Verification::where('phone', $phone)->first();
        if ($verification) {
            $verification->update($array);
        } else {
            Verification::create($array);
        }

        return true;
    }

    public function canSendOTP($email)
    {
        $verification = Verification::where('email', $email)->first();

        if ($verification) {
            if ($verification->expired_at > date('Y-m-d H:i:s', strtotime('now'))) {
                return false;
            } else {
                $verification->delete();

                return true;
            }
        } else {
            return true;
        }
    }

    public function canSendOTP1($phone_number)
    {
        $verification = Verification::where('phone', $phone_number)->where('sms', true)->first();

        if ($verification) {
            if ($verification->expired_at > date('Y-m-d H:i:s', strtotime('now'))) {
                return false;
            } else {
                $verification->delete();

                return true;
            }
        } else {
            return true;
        }
    }

    public function canSendOTP2($phone_number)
    {
        $verification = Verification::where('phone', $phone_number)->where('whatsapp', true)->first();

        if ($verification) {
            if ($verification->expired_at > date('Y-m-d H:i:s', strtotime('now'))) {
                return false;
            } else {
                $verification->delete();

                return true;
            }
        } else {
            return true;
        }
    }

    public function verifyOTP($data)
    {
        $email = $data['email'];
        $phone = $data['phone'];
        $author = $data['author'];
        $for_login = $data['for_login'];
        $result = [];
        $verificationEmail = Verification::whereEmail($email)->where('code', $data['verification_code'])->first();
        $verificationPhone = Verification::wherePhone($phone)->where('code', $data['verification_code'])->first();

        if ($verificationPhone == null && $verificationEmail == null) {
            return false;
        }
        if ($email) {

            if ($verificationEmail) {
                if ($for_login) {
                    $user = User::where('email', $author)->first();
                    $user->code_otp = $data['verification_code'];
                    $user->save();
                }
                $verificationPhone?->delete();
                $verificationEmail->delete();
                $result['email'] = true;
            } else {
                $result['email'] = false;
            }
        }

        if ($phone) {

            if ($verificationPhone) {
                if ($for_login) {
                    $user = User::where('email', $author)->first();
                    $user->code_otp = $data['verification_code'];
                    $user->save();
                }
                $verificationEmail?->delete();
                $verificationPhone->delete();
                $result['SMS_WHATSAPP'] = true;

            } else {
                $result['SMS_WHATSAPP'] = false;
            }
        }

        return $result;
    }

    public function sendMailOTP(string $email, $code)
    {
        if (! $this->canSendOTP($email)) {
            return $this->errorMsg;
        }
        $this->createOTPExpirationTime($email, $code);
        $user = User::where('email', $email)->first();
        Mailer::sendSimple('emails.otp', ['code' => $code, 'user' => $user], 'Code OTP de connexion', 'Utilisateur SPFCT', $email);

        return true;

    }

    public function sendSMSOTP($phone, $code)
    {
        if (! $this->canSendOTP1($phone)) {
            return $this->errorMsg;
        }
        $this->createOTPExpirationTime1($phone, $code);

        $result = $this->twService->sendSms($phone, "Votre code de connexion est: $code");

        return true;

    }

    public function sendWhatsappSms($phone, $code)
    {
        if (! $this->canSendOTP2($phone)) {
            return $this->errorMsg;
        }
        $this->createOTPExpirationTime2($phone, $code);
        $result = $this->twService->sendWhatsappSms($phone, "Votre code de connexion est: $code", $code);

        return true;

    }
}
