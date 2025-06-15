<?php

namespace App\Documentation\Model;

/**
 * Class SendOTP
 *
 * @OA\Schema(
 *     schema="SendOTP",
 *     title="SendOTP class",
 *     description="SendOTP class",
 * )
 */
class SendOTP
{
    /**
     * @OA\Property()
     *
     * @var int
     */
    private $phone;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *  type="array",
     *
     *  @OA\Items(
     *   type="string",
     * )
     * )
     */
    private $canal;
}
