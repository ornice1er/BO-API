<?php

namespace App\Documentation\Model;

/**
 * Class Municipality
 *
 * @OA\Schema(
 *     schema="Municipality",
 *     title="Municipality class",
 *     description="Municipality class",
 * )
 */
class Municipality
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $code;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $edition_id;

    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $project_id;
}
