<?php

namespace App\Documentation\Model;

/**
 * Class RoleCreate
 *
 * @OA\Schema(
 *     schema="RoleCreate",
 *     title="RoleCreate class",
 *     description="RoleCreate class",
 * )
 */
class RoleCreate
{
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
