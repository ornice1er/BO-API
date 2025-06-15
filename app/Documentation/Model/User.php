<?php

namespace App\Documentation\Model;

/**
 * Class User
 *
 * @OA\Schema(
 *     schema="User",
 *     title="User class",
 *     description="User class",
 * )
 */
class User
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
    private $code;

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
     * @OA\Property()
     *
     * @var string
     */
    private $photo;

    /**
     * @OA\Property()
     *
     * @var string
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
    private $project_id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $role;

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
    private $push_token;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $comment;
}
