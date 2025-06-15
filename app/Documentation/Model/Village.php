<?php

namespace App\Documentation\Model;

/**
 * Class Village
 *
 * @OA\Schema(
 *     schema="Village",
 *     title="Village class",
 *     description="Village class",
 * )
 */
class Village
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
}
