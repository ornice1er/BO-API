<?php

namespace App\Documentation\Model;

/**
 * Class ExportCreate
 *
 * @OA\Schema(
 *     schema="ExportCreate",
 *     title="ExportCreate class",
 *     description="ExportCreate class",
 * )
 */
class ExportCreate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $title;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $subtitle;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $description;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $table_header;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $table_body;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $content;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $conclusion;
}
