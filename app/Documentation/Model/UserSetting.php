<?php

namespace App\Documentation\Model;

/**
 * Class UserSetting
 *
 * @OA\Schema(
 *     schema="UserSetting",
 *     title="UserSetting class",
 *     description="UserSetting class",
 * )
 */
class UserSetting
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
     * @var int
     */
    private $user_id;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $use_2FA;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $accept_notification;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $notification_list;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $mode_2FA;
}
