<?php

namespace App\Documentation\Model;

/**
 * Class ChangeFirstPasswordParams
 *
 * @OA\Schema(
 *     schema="ChangeFirstPasswordParams",
 *     title="ChangeFirstPasswordParams",
 *     description="Request body for changing user password",
 * )
 */
class ChangeFirstPasswordParams
{
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
