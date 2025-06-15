<?php

namespace App\Documentation\Model;

/**
 * Class RecoveryPasswordParams
 *
 * @OA\Schema(
 *     schema="RecoveryPasswordParams",
 *     title="RecoveryPasswordParams",
 *     description="Request body for changing user password",
 * )
 */
class RecoveryPasswordParams
{
    /**
     * @OA\Property(
     *     description="Token de validation",
     *     example="new_password123",
     *     type="string",
     * )
     *
     * @var string
     */
    private $token;

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
