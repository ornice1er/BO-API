<?php

namespace App\Documentation\Model;

/**
 * Class MunicipalityCreate
 *
 * @OA\Schema(
 *     schema="MunicipalityCreate",
 *     title="MunicipalityCreate class",
 *     description="MunicipalityCreate class",
 * )
 */
class MunicipalityCreate
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
    private $department_id;
}
