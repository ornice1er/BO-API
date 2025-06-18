<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdherantCmps extends Model
{
    protected $guarded = [];


    public function members()
    {
        return $this->hasMany(AdherantMemberCmps::class,'adh_id');
    }
}
