<?php

namespace App\Documentation\Model;

/**
 * Class UploadFile
 *
 * @OA\Schema(
 *     schema="UploadFile",
 *     title="UploadFile class",
 *     description="UploadFile class schema",
 * )
 */
class UploadFile
{
    /**
     * @OA\Property(
     *     description="le chemin du fichier",
     *     type="string"
     * )
     */
    private $path;

    /**
     * @OA\Property(
     *     description="Fichier  (uploadé)",
     *     type="string",
     *     format="binary",
     * )
     */
    private $input;
}
