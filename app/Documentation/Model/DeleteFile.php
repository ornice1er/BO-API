<?php

namespace App\Documentation\Model;

/**
 * Class DeleteFile
 *
 * @OA\Schema(
 *     schema="DeleteFile",
 *     title="DeleteFile class",
 *     description="DeleteFile class schema",
 * )
 */
class DeleteFile
{
    /**
     * @OA\Property(
     *     description="le chemin complet du fichier",
     *     type="string"
     * )
     */
    private $path;
}
