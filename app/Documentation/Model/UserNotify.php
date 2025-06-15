<?php

namespace App\Documentation\Model;

/**
 * Class UserNotify
 *
 * @OA\Schema(
 *     schema="UserNotify",
 *     title="UserNotify class",
 *     description="UserNotify class",
 * )
 */
class UserNotify
{
    /**
     * @OA\Property(
     *      type="string",
     *      description=""
     * )
     *
     * @var string
     */
    private $category;

    /**
     * @OA\Property(
     *
     * @OA\Items(
     * type="string",
     *  enum={"DATABASE","MAIL","PUSH"}
     * )
     * )
     *
     * @var array
     */
    private $canal;

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
    private $content;

    /**
     * @OA\Property(
     *
     *     @OA\Items(
     *      type="integer"
     *      )
     * )
     *
     * @var array
     */
    private $users;
}
