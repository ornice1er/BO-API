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
class UserUpdate
{
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
    private $firstname;

    /**
     * @OA\Property(
     *     format="date"
     * )
     *
     * @var string
     */
    private $birthdate;

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
    private $password;

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
    private $phone;

    /**
     * @OA\Property(
     *     type="string",
     * )
     */
    private $photo;

    /**
     * @OA\Property(
     *     type="string",
     * )
     */
    private $cv;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $token;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $code_otp;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $residence_place;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $education_level;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $nb_children;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $computer_skills;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $reference_person;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $comment;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $municipality_id;

    /**
     * @OA\Property(
     *
     * @OA\Items(
     * type="integer"
     * )
     * )
     *
     * @var array
     */
    private $spoken_languages;

    /**
     * @OA\Property(
     *
     * @OA\Items(
     * type="integer"
     * )
     * )
     *
     * @var array
     */
    private $understood_languages;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $statut_agent_id;
}
