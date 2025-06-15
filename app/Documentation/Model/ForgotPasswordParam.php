<?php

namespace App\Documentation\Model;

/**
 * Class ForgotPasswordParam
 *
 * @OA\Schema(
 *     title="ForgotPasswordParam",
 *     description="ForgotPassword  model",
 * )
 */
class ForgotPasswordParam
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
    private $url;
}
