<?php

namespace App\Documentation\Model;

/**
 * Class ResetPasswordParams
 *
 * @OA\Schema(
 *     schema="ResetPasswordParams",
 *     title="ResetPasswordParams",
 *     description="Request body for changing user password",
 * )
 */
class ResetPasswordParams
{
    /**
     * @OA\Property(
     *     description="Email used for changing password",
     *     example="new_password123",
     *     type="string",
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     description="New password of the user",
     *     example="new_password123",
     *     type="string",
     *     format="password"
     * )
     *
     * @var string
     */
    private $password;

    /**
     * @OA\Property(
     *     description="Password confirmation",
     *     example="new_password123",
     *     type="string",
     *     format="password"
     * )
     *
     * @var string
     */
    private $password_confirmation;
}
