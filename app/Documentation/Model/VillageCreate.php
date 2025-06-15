<?php

namespace App\Documentation\Model;

/**
 * Class VillageCreate
 *
 * @OA\Schema(
 *     schema="VillageCreate",
 *     title="VillageCreate class",
 *     description="VillageCreate class",
 * )
 */
class VillageCreate
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
    private $district_id;
}
