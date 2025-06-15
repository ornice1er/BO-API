<?php

namespace App\Documentation\Model;

/**
 * Class PermissionCreate
 *
 * @OA\Schema(
 *     schema="PermissionCreate",
 *     title="PermissionCreate class",
 *     description="PermissionCreate class",
 * )
 */
class PermissionCreate
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

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $group_name;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $show_edit;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $show_only;
}
