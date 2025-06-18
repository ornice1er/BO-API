<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationExistence extends Model
{
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(AttestationExistenceFile::class,'ae_id');
    }
}
