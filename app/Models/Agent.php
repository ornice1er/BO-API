<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{

    protected $guarded = [];


    public function fonctionAgent()
    {
        return $this->belongsTo(FonctionAgent::class,'fonction_agent_id');
    }

    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class,'unite_admin_id');
    }

    public function entiteAdmin()
    {
        return $this->belongsTo(EntiteAdmin::class,'entite_admin_id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'agent_id');
    }
}

