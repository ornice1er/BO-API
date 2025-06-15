<?php

namespace App\Documentation\Model;

/**
 * Class Role
 *
 * @OA\Schema(
 *     schema="Role",
 *     title="Role class",
 *     description="Role class",
 * )
 */
class Role
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
    private $name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $guard_name;
}
