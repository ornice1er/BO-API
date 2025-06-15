<?php

namespace App\Documentation\Model;

/**
 * Class GetUserPermissionRequest
 *
 * @OA\Schema(
 *     title="GetUserPermissionRequest",
 *     description="GetUserPermissionRequest  model",
 * )
 */
class GetUserPermissionRequest
{
    /**
     * @OA\Property()
     *
     * @var int
     */
    private $project_id;

    /**
     * @OA\Property(
     *
     * @OA\Items(
     * type="string"
     * )
     * )
     *
     * @var array
     */
    private $label_names;
}
