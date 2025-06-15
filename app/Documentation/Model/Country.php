<?php

namespace App\Documentation\Model;

/**
 * Class Country
 *
 * @OA\Schema(
 *     schema="Country",
 *     title="Country class",
 *     description="Country class",
 * )
 */
class Country
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
