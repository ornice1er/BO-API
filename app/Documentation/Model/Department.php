<?php

namespace App\Documentation\Model;

/**
 * Class Department
 *
 * @OA\Schema(
 *     schema="Department",
 *     title="Department class",
 *     description="Department class",
 * )
 */
class Department
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
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $country_id;
}
