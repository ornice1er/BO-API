<?php

namespace App\Documentation\Model;

/**
 * Class UserSettingUpdate
 *
 * @OA\Schema(
 *     schema="UserSettingUpdate",
 *     title="UserSettingUpdate class",
 *     description="UserSettingUpdate class",
 * )
 */
class UserSettingUpdate
{
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
