<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationDeServiceFait extends Model
{
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(AttestationDeServiceFaitFile::class,'asf_id');
    }
}
