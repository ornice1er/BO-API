<?php

namespace App\Documentation\Model;

/**
 * Class LoginRequest
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     title="LoginRequest",
 *     description="Request body for user login",
 * )
 */
class LoginRequest
{
    /**
     * @OA\Property(
     *     description="The email of the user",
     *     example="user@example.com",
     *     type="string",
     *     format="email"
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     description="The password of the user",
     *     example="password123",
     *     type="string",
     *     format="password"
     * )
     *
     * @var string
     */
    private $password;

    /**
     * @OA\Property(
     *  description="Nature de device",
     *  example="web",
     *  type="string",
     *  enum={"web", "mobile"}
     * )
     *
     * @var string
     */
    private $device;
}
