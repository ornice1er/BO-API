<?php

namespace App\Documentation\Model;

/**
 * Class Menu
 *
 * @OA\Schema(
 *     schema="Menu",
 *     title="Menu class",
 *     description="Menu class",
 * )
 */
class Menu
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
    private $key;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $path;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $is_active;
}
