<?php

namespace App\Documentation\Model;

/**
 * Class Setting
 *
 * @OA\Schema(
 *     schema="Setting",
 *     title="Setting class",
 *     description="Setting class",
 * )
 */
class Setting
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
     * @OA\Property(
     *
     * )
     *
     * @var string
     */
    private $key;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $value;
}
