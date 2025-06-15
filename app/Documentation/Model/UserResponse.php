<?php

namespace App\Documentation\Model;

/**
 * Class UserResponse
 *
 * @OA\Schema(
 *     title=" UserResponse",
 *     description="User data",
 * )
 */
class UserResponse
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $name;

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
    private $email_verified_at;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $active;
}
