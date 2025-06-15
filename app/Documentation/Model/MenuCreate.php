<?php

namespace App\Documentation\Model;

/**
 * Class MenuCreate
 *
 * @OA\Schema(
 *     schema="MenuCreate",
 *     title="MenuCreate class",
 *     description="MenuCreate class",
 * )
 */
class MenuCreate
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
    private $key;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $path;
}
