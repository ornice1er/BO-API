<?php

namespace App\Documentation\Model;

/**
 * Class Project
 *
 * @OA\Schema(
 *     schema="Project",
 *     title="Project class",
 *     description="Project class",
 * )
 */
class Project
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
     * @var int
     */
    private $pc_id;
}
