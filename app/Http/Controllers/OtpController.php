<?php

namespace App\Http\Controllers;

use App\Http\Requests\OTP\SendOTPRequest;
use App\Http\Requests\OTP\VerifyOTPRequest;
use App\Services\OTPService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(?OTPService $otpService = null)
    {
        $this->otpService = $otpService;
    }

    /** @OA\POST(
     *      path="/send-otp",
     *      operationId="Send OTP",
     *      tags={"OTP"},
     *     security={{"JWT":{}}},
     *      summary="Allow to send otp ",
     *      description="You can send OTP by different channel like SMS, WHATSAPP and EMAIL",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ResponseData"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/ResponseData")
     *     ),
     *
     *    @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/SendOTP")
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
    public function sendOTP(SendOTPRequest $request)
    {

        try {

            $code = random_int(100000, 999999);

            $result = [];

            if (array_search('SMS', $request->canal) !== false) {

                $result['SMS'] = $this->otpService->sendSMSOTP($request->phone, $code);
            }
            if (array_search('APP', $request->canal) !== false) {

                $result['APP'] = $this->otpService->sendWhatsappSms($request->phone, $code);
            }
            if (array_search('EMAIL', $request->canal) !== false) {

                $result['EMAIL'] = $this->otpService->sendMailOTP($request->email, $code);
            }
            if ($result === false) {
                return Common::error('Echec d\'envoi du code OTP', $result);
            }

            return Common::success('Code OTP envoyé avec succès', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\POST(
     *      path="/verify-otp",
     *      operationId="Verify OTP",
     *      tags={"OTP"},
     *      summary="Verify to vérification otp ",
     *      description="You can verify OTP send to channel like SMS, WHATSAPP and EMAIL",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ResponseData"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/ResponseData")
     *     ),
     *
     *    @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/VerifyOTP")
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
    public function verifyOTP(VerifyOTPRequest $request)
    {

        try {
            $result = $this->otpService->verifyOTP($request->all());
            if ($result === false) {
                return Common::error('Code OTP non vérifé', $result);
            }

            return Common::success('Code OTP vérifé avec suucès', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }

    }
}
