<?php

namespace App\Documentation\Model;

/**
 * Class ProjectCategory
 *
 * @OA\Schema(
 *     schema="ProjectCategory",
 *     title="ProjectCategory class",
 *     description="ProjectCategory class",
 * )
 */
class ProjectCategory
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

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $is_active;
}
