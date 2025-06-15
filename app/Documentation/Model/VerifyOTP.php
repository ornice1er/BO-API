<?php

namespace App\Documentation\Model;

/**
 * Class VerifyOTP
 *
 * @OA\Schema(
 *     schema="VerifyOTP",
 *     title="VerifyOTP class",
 *     description="VerifyOTP class",
 * )
 */
class VerifyOTP
{
    /**
     * @OA\Property()
     *
     * @var int
     */
    private $phone;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $verification_code;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $for_login;
}
