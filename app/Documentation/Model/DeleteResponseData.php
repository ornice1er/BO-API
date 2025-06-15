<?php

namespace App\Documentation\Model;

/**
 * Class DeleteResponseData
 *
 * @OA\Schema(
 *     title=" Output delete",
 *     description="Output delete model",
 * )
 */
class DeleteResponseData
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
