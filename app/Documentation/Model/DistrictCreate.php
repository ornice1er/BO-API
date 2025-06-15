<?php

namespace App\Documentation\Model;

/**
 * Class DistrictCreate
 *
 * @OA\Schema(
 *     schema="DistrictCreate",
 *     title="DistrictCreate class",
 *     description="DistrictCreate class",
 * )
 */
class DistrictCreate
{
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
    private $municipality_id;
}
