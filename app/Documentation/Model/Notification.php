<?php

namespace App\Documentation\Model;

/**
 * Class Notification
 *
 * @OA\Schema(
 *     title="Notification",
 *     description="Notification",
 * )
 */
class Notification
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var string
     */
    private $id;

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
     * @var string
     */
    private $data;

    /**
     * @OA\Property()
     *
     * @var date
     */
    private $read_at;
}
