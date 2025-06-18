<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationDeSoin extends Model
{
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(AttestationDeSoinFile::class,'ascmps_id');
    }
}
