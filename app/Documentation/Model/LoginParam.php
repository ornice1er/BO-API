<?php

namespace App\Documentation\Model;

/**
 * Class LoginParam
 *
 * @OA\Schema(
 *     title="LoginParam",
 *     description="Login  model",
 * )
 */
class LoginParam
{
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
    private $password;

    /**
     * @OA\Property(enum={"web", "mobile"})
     *
     * @var string
     */
    private $device;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $code_otp;
}
