<?php

namespace App\Documentation\Model;

/**
 * Class UserAuth
 *
 * @OA\Schema(
 *     schema="UserAuth",
 *     title="UserAuth class",
 *     description="UserAuth class",
 * )
 */
class UserAuth
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
    private $email;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $passowrd;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $new_connexion_canal;
}
