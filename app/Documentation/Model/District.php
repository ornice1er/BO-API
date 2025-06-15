<?php

namespace App\Documentation\Model;

/**
 * Class District
 *
 * @OA\Schema(
 *     schema="District",
 *     title="District class",
 *     description="District class",
 * )
 */
class District
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
}
