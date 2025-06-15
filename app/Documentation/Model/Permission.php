<?php

namespace App\Documentation\Model;

/**
 * Class Permission
 *
 * @OA\Schema(
 *     schema="Permission",
 *     title="Permission class",
 *     description="Permission class",
 * )
 */
class Permission
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
