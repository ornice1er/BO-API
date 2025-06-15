<?php

namespace App\Documentation\Model;

/**
 * Class ResendEmailConfirm
 *
 * @OA\Schema(
 *     title="ResendEmailConfirm",
 *     description="ResendEmailConfirm",
 * )
 */
class ResendEmailConfirm
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $url;
}
