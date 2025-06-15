<?php

namespace App\Documentation\Model;

/**
 * Class ResponseData
 *
 * @OA\Schema(
 *     title=" Output",
 *     description="Output model",
 * )
 */
class ResponseData
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $message;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    private $status;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $data;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $warning;

    /**
     * @OA\Property(
     *  type="array",
     *
     *  @OA\Items(
     *   type="object",
     *
     *   @OA\Property(type="string", property="id", description=""),
     *   @OA\Property(type="string", property="response", description=""),
     * )
     * )
     */
    private $error_list;
}
