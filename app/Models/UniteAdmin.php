<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniteAdmin extends Model
{
    protected $guarded = [];

    public function typeUniteAdmin()
    {
        return $this->belongsTo(TypeUniteAdmin::class,'type_unite_admin_id');
    }
    public function agent()
    {
        return $this->hasOne(Agent::class,'unite_admin_id');
    }
}
