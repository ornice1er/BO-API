<?php

namespace App\Documentation\Model;

/**
 * Class UserProjectCreate
 *
 * @OA\Schema(
 *     schema="UserProjectCreate",
 *     title="UserProjectCreate class",
 *     description="UserProjectCreate class",
 * )
 */
class UserProjectCreate
{
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
