<?php

namespace App\Documentation\Model;

/**
 * Class ResetPasswordLinkParams
 *
 * @OA\Schema(
 *     schema="ResetPasswordLinkParams",
 *     title="ResetPasswordLinkParams",
 *     description="Request body for changing user password",
 * )
 */
class ResetPasswordLinkParams
{
    /**
     * @OA\Property(
     *     description="Email where a link can sent",
     *     example="user@gmail.com",
     *     type="string",
     * )
     *
     * @var string
     */
    private $email;
}
