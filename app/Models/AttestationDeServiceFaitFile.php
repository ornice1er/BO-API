<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationDeServiceFaitFile extends Model
{
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(AttestationNonLitigeFile::class,'atn_id');
    }
}
