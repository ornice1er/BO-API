<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationDesServiceAux extends Model
{
    

    protected $guarded = [];


    public function files()
    {
        return $this->hasMany(ValidationDesServiceAuxFile::class,'vsa_id');
    }
}
