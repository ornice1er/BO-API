<?php

namespace App\Documentation\Model;

/**
 * Class ProjectCreate
 *
 * @OA\Schema(
 *     schema="ProjectCreate",
 *     title="ProjectCreate class",
 *     description="ProjectCreate class",
 * )
 */
class ProjectCreate
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
    private $description;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $pc_id;
}
