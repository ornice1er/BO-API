<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratP extends Model
{
    protected $table="contrat_ps";
    protected $guarded = [];


    public function files()
    {
        return $this->hasMany(ContratFile::class,'contrat_id');

    }

}
