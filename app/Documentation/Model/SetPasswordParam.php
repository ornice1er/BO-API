<?php

namespace App\Documentation\Model;

/**
 * Class SetPasswordParam
 *
 * @OA\Schema(
 *     title="SetPasswordParam",
 *     description="SetPassword  model",
 * )
 */
class SetPasswordParam
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $last_password;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $new_password;
}
