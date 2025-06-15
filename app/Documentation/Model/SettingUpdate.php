<?php

namespace App\Documentation\Model;

/**
 * Class SettingUpdate
 *
 * @OA\Schema(
 *     schema="SettingUpdate",
 *     title="SettingUpdate class",
 *     description="SettingUpdate class",
 * )
 */
class SettingUpdate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $key;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $value;
}
