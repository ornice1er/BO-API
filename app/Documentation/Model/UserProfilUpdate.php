<?php

namespace App\Documentation\Model;

/**
 * Class UserUpdate
 *
 * @OA\Schema(
 *     title="UserUpdate",
 *     description="UserUpdate",
 * )
 */
class UserProfilUpdate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $firstname;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $lastname;

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
    private $phone;

    /**
     * @OA\Property()
     *
     * @var date
     */
    private $birthdate;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $birthplace;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $address;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $photo;

    /**
     * @OA\Property(
     *     type="string",
     * )
     */
    private $cv;
}
