<?php

namespace App\Documentation\Model;

/**
 * Class ProjectCategoryCreate
 *
 * @OA\Schema(
 *     schema="ProjectCategoryCreate",
 *     title="ProjectCategoryCreate class",
 *     description="ProjectCategoryCreate class",
 * )
 */
class ProjectCategoryCreate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $name;
}
