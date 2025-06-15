<?php

namespace App\Documentation\Model;

/**
 * Class ChangePasswordRequest
 *
 * @OA\Schema(
 *     schema="ChangePasswordRequest",
 *     title="ChangePasswordRequest",
 *     description="Request body for changing user password",
 * )
 */
class ChangePasswordRequest
{
    /**
     * @OA\Property(
     *     description="Old password of the user",
     *     example="old_password123",
     *     type="string",
     *     format="password"
     * )
     *
     * @var string
     */
    private $old_password;

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
    private $new_password;

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
    private $new_password_confirmation;
}
