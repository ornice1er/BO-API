<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    protected $guarded = [];

    public function personals()
    {
        return $this->hasMany(EstablishmentPersonnel::class,'etablishment_id');
    }
}
