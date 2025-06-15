<?php

namespace App\Documentation\Model;

/**
 * Class Log
 *
 * @OA\Schema(
 *     schema="Log",
 *     title="Log class",
 *     description="Log class",
 * )
 */
class Log
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
    private $action_name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $description;
}
