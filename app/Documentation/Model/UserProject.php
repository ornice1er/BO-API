<?php

namespace App\Documentation\Model;

/**
 * Class UserProject
 *
 * @OA\Schema(
 *     schema="UserProject",
 *     title="UserProject class",
 *     description="UserProject class",
 * )
 */
class UserProject
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
    private $project_id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $user_id;
}
