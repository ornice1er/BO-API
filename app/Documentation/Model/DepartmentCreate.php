<?php

namespace App\Documentation\Model;

/**
 * Class DepartmentCreate
 *
 * @OA\Schema(
 *     schema="DepartmentCreate",
 *     title="DepartmentCreate class",
 *     description="DepartmentCreate class",
 * )
 */
class DepartmentCreate
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
    private $country_id;
}
