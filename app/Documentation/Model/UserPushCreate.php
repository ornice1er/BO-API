<?php

namespace App\Documentation\Model;

/**
 * Class UserPushCreate
 *
 * @OA\Schema(
 *     schema="UserPushCreate",
 *     title="UserPushCreate class",
 *     description="UserPushCreate class",
 * )
 */
class UserPushCreate
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $user_id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $push_token;
}
