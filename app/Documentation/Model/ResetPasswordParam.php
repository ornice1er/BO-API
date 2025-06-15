<?php

namespace App\Documentation\Model;

/**
 * Class ResetPasswordParam
 *
 * @OA\Schema(
 *     title="ResetPasswordParam",
 *     description="ResetPasswordParam  model",
 * )
 */
class ResetPasswordParam
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $token;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $password;
}
