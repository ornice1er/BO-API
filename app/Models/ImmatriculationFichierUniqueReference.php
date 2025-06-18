<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImmatriculationFichierUniqueReference extends Model
{
    protected $guarded = [];


    public function files()
    {
        return $this->hasMany(ImmatriculationFichierUniqueReferenceFile::class,'ifur_id');
    }
}
