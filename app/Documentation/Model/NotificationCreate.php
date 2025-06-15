<?php

namespace App\Documentation\Model;

/**
 * Class NotificationCreate
 *
 * @OA\Schema(
 *     title="NotificationCreate",
 *     description="NotificationCreate",
 * )
 */
class NotificationCreate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $notifiable_type;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $notifiable_id;

    /**
     * @OA\Property()
     *
     * @var array()
     */
    private $data;

    /**
     * @OA\Property()
     *
     * @var date
     */
    private $read_at;
}
