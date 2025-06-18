<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EntiteAdmin extends Model
{
    protected $guarded = [];

    public function prestations()
    {
        
        return $this->hasMany(Prestation::class,'entite_admin_id');
    }
}
