<?php

namespace App\Documentation\Model;

/**
 * Class CountryList
 *
 * @OA\Schema(
 *     schema="CountryList",
 *     title="CountryList class",
 *     description="CountryList class",
 * )
 */
class CountryList
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

    /**
     * @OA\Property(
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/Country")
     * )
     */
    private $data;
}
