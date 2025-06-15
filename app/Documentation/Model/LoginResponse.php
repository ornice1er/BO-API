<?php

namespace App\Documentation\Model;

/**
 * Class LoginResponse
 *
 * @OA\Schema(
 *     title=" LoginResponse",
 *     description="Login response",
 * )
 */
class LoginResponse
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $access_token;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $token_type;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $expires_in;
}
