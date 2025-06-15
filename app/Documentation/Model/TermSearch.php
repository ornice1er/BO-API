<?php

namespace App\Documentation\Model;

/**
 * Class TermSearch
 *
 * @OA\Schema(
 *     schema="TermSearch",
 *     title="TermSearch class",
 *     description="TermSearch class",
 * )
 */
class TermSearch
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $term;
}
