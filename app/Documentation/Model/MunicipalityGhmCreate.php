<?php

namespace App\Documentation\Model;

/**
 * Class MunicipalityGhmCreate
 *
 * @OA\Schema(
 *     schema="MunicipalityGhmCreate",
 *     title="MunicipalityGhmCreate class",
 *     description="MunicipalityGhmCreate class",
 * )
 */
class MunicipalityGhmCreate
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
    private $project_id;

    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $edition_id;
}
