<?php

namespace App\Documentation\Model;

/**
 * Class CountryCreate
 *
 * @OA\Schema(
 *     schema="CountryCreate",
 *     title="CountryCreate class",
 *     description="CountryCreate class",
 * )
 */
class CountryCreate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $name;
}
